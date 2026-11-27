<?php

namespace App\Mail;

use App\Mail\Concerns\SentInBackground;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Asks a customer to review what they bought, a few days after delivery.
 * Sent once per order, in the language the order was placed in.
 */
class ReviewRequest extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->locale($this->order->locale ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('shop.contact_email'), config('app.name'))],
            subject: __('email.review_request.subject', ['number' => $this->order->number], $this->locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.review-request',
            text: 'emails.orders.review-request-text',
            with: [
                'order' => $this->order,
                'products' => $this->order->reviewableProducts(),
                'reviewUrl' => $this->order->reviewUrl($this->locale),
                'isRtl' => $this->locale === 'ar',
            ],
        );
    }
}
