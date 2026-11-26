<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            Password::sendResetLink(['email' => mb_strtolower(trim($request->input('email')))]);
        } catch (\Throwable $e) {
            Log::error('Password reset email could not be sent.', ['error' => $e->getMessage()]);
        }

        // The same answer whether or not an account exists (and whether the
        // broker throttled the request), so the form cannot be used to find out
        // who is a customer.
        return back()->with('status', __('passwords.sent_if_exists'));
    }
}
