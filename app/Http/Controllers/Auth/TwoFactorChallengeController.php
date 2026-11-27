<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Second step of an admin login: the code sent by email.
 */
class TwoFactorChallengeController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $pending = TwoFactor::pending('login');
        if (! $pending) {
            return redirect()->route('login');
        }

        $user = User::find($pending['user_id']);

        return view('auth.two-factor-challenge', [
            'email' => $user ? self::maskEmail($user->email) : '',
            'resendWait' => TwoFactor::resendWait('login'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|max:20']);

        $pending = TwoFactor::pending('login');
        $user = $pending ? User::find($pending['user_id']) : null;
        if (! $user) {
            TwoFactor::forget('login');

            return redirect()->route('login');
        }

        $result = TwoFactor::check('login', $request->input('code'));
        if ($result !== 'ok') {
            return back()->withErrors(['code' => __('two_factor.challenge.' . $result)]);
        }

        TwoFactor::forget('login');
        Auth::guard('web')->login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->regenerate();

        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->intended(route('home'));
    }

    public function resend(): RedirectResponse
    {
        $pending = TwoFactor::pending('login');
        $user = $pending ? User::find($pending['user_id']) : null;
        if (! $user) {
            return redirect()->route('login');
        }

        $wait = TwoFactor::resendWait('login');
        if ($wait === null) {
            // Too many emails for this attempt: start again with the password.
            TwoFactor::forget('login');

            return redirect()->route('login')->withErrors(['email' => __('two_factor.challenge.start_again')]);
        }
        if ($wait > 0) {
            return back()->withErrors(['code' => __('two_factor.challenge.wait', ['seconds' => $wait])]);
        }

        try {
            TwoFactor::send($user, 'login', ['remember' => (bool) ($pending['remember'] ?? false)]);
        } catch (\Throwable $e) {
            Log::error('Two-step verification code could not be emailed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return back()->withErrors(['code' => __('two_factor.send_failed')]);
        }

        return back()->with('status', __('two_factor.challenge.resent'));
    }

    /** "ab****@example.com": enough to recognise, not to read off a screen. */
    public static function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($name, 0, min(2, max(1, mb_strlen($name) - 1)));

        return $visible . str_repeat('*', max(1, mb_strlen($name) - mb_strlen($visible))) . '@' . $domain;
    }
}
