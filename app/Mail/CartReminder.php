<?php

namespace App\Mail;

use App\Console\Commands\SendCartReminders;
use App\Mail\Concerns\SentInBackground;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * "You left something in your cart" (first or second reminder), with a
 * one-click way to stop these emails.
 */
class CartReminder extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

    public function __construct(public User $user, public int $number = 1)
    {
        $this->locale($this->user->locale ?: config('app.locale'));
    }

    public static function unsubscribeUrl(User $user, string $locale): string
    {
        return URL::signedRoute('cart-reminders.unsubscribe', ['locale' => $locale, 'user' => $user->id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('shop.contact_email'), config('app.name'))],
            subject: __($this->number > 1 ? 'email.cart_reminder.subject_second' : 'email.cart_reminder.subject', [], $this->locale),
        );
    }

    /** One-click unsubscribe for mail apps (RFC 8058). */
    public function headers(): Headers
    {
        $url = self::unsubscribeUrl($this->user, $this->locale);

        return new Headers(text: [
            'List-Unsubscribe' => '<' . $url . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cart.reminder',
            text: 'emails.cart.reminder-text',
            with: [
                'user' => $this->user,
                'lines' => SendCartReminders::lines($this->user),
                'cartUrl' => route('cart.index', ['locale' => $this->locale]),
                'unsubscribeUrl' => self::unsubscribeUrl($this->user, $this->locale),
                'second' => $this->number > 1,
                'isRtl' => $this->locale === 'ar',
            ],
        );
    }
}
