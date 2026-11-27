<?php

namespace App\Mail;

use App\Models\User;
use App\Mail\Concerns\SentInBackground;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Password reset link, in the language of the page the request came from.
 */
class PasswordResetEmail extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

    public function __construct(public User $user, public string $token, ?string $locale = null)
    {
        $this->locale($locale ?: app()->getLocale() ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('shop.contact_email'), config('app.name'))],
            subject: __('email.password_reset.subject', [], $this->locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.password-reset',
            text: 'emails.auth.password-reset-text',
            with: [
                'user' => $this->user,
                'isRtl' => $this->locale === 'ar',
                'resetUrl' => route('password.reset', [
                    'locale' => $this->locale,
                    'token' => $this->token,
                    'email' => $this->user->getEmailForPasswordReset(),
                ]),
                'minutes' => (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60),
                'forgotUrl' => route('password.request', ['locale' => $this->locale]),
            ],
        );
    }
}
