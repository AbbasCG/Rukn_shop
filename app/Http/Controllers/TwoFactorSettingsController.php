<?php

namespace App\Http\Controllers;

use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Admins turn two-step verification on (after proving that the codes reach
 * their inbox) or off (with their password), from the profile page.
 */
class TwoFactorSettingsController extends Controller
{
    /** Step 1: email a code to confirm. */
    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);

        if ($user->usesTwoFactor()) {
            return $this->backToSection();
        }

        $wait = TwoFactor::resendWait('enable');
        if ($wait === null || $wait > 0) {
            return $this->backToSection()->withErrors(['code' => $wait === null
                ? __('two_factor.settings.too_many_sends')
                : __('two_factor.challenge.wait', ['seconds' => $wait])], 'twoFactor');
        }

        try {
            TwoFactor::send($user, 'enable');
        } catch (\Throwable $e) {
            Log::error('Two-step verification code could not be emailed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return $this->backToSection()->withErrors(['code' => __('two_factor.send_failed')], 'twoFactor');
        }

        return $this->backToSection()->with('two_factor_status', __('two_factor.settings.code_sent'));
    }

    /** Step 2: the code is right, so codes arrive: turn it on. */
    public function confirm(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);
        $request->validateWithBag('twoFactor', ['code' => 'required|string|max:20']);

        $pending = TwoFactor::pending('enable');
        if (! $pending || (int) $pending['user_id'] !== $user->id) {
            return $this->backToSection()->withErrors(['code' => __('two_factor.challenge.expired')], 'twoFactor');
        }

        $result = TwoFactor::check('enable', $request->input('code'));
        if ($result !== 'ok') {
            return $this->backToSection()->withErrors(['code' => __('two_factor.challenge.' . $result)], 'twoFactor');
        }

        TwoFactor::forget('enable');
        $user->forceFill(['two_factor_enabled_at' => now()])->save();

        return $this->backToSection()->with('two_factor_status', __('two_factor.settings.enabled'));
    }

    public function cancel(): RedirectResponse
    {
        TwoFactor::forget('enable');

        return $this->backToSection();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);
        $request->validateWithBag('twoFactor', ['password' => ['required', 'current_password']]);

        $user->forceFill(['two_factor_enabled_at' => null])->save();

        return $this->backToSection()->with('two_factor_status', __('two_factor.settings.disabled'));
    }

    private function backToSection(): RedirectResponse
    {
        return redirect()->to(route('profile.edit') . '#two-factor');
    }
}
