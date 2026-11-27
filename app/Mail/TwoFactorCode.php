<?php

namespace App\Mail;

use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The login code for the admin panel. Not queued on purpose: the admin is
 * waiting on the code page, and the queue only runs once a minute.
 */
class TwoFactorCode extends Mailable
{
    public function __construct(public User $user, public string $code, ?string $locale = null)
    {
        $this->locale($locale ?: app()->getLocale() ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('two_factor.email.subject', ['code' => $this->code], $this->locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.two-factor-code',
            text: 'emails.auth.two-factor-code-text',
            with: [
                'user' => $this->user,
                'code' => $this->code,
                'minutes' => TwoFactor::MINUTES,
                'isRtl' => $this->locale === 'ar',
            ],
        );
    }
}
