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
 * Sent once after registration, in the language the customer signed up in.
 */
class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

    public function __construct(public User $user, ?string $locale = null)
    {
        $this->locale($locale ?: app()->getLocale() ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('shop.contact_email'), config('app.name'))],
            subject: __('email.welcome.subject', ['name' => $this->user->name], $this->locale),
        );
    }

    public function content(): Content
    {
        $link = fn (string $route, array $params = []) => route($route, ['locale' => $this->locale] + $params);

        return new Content(
            view: 'emails.welcome.welcome',
            text: 'emails.welcome.welcome-text',
            with: [
                'user' => $this->user,
                'isRtl' => $this->locale === 'ar',
                'shopUrl' => $link('products.index'),
                'ordersUrl' => $link('orders.index'),
                'profileUrl' => $link('profile.edit'),
                'contactUrl' => $link('contact'),
            ],
        );
    }
}
