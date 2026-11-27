<?php

namespace App\Mail;

use App\Models\Order;
use App\Mail\Concerns\SentInBackground;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the customer right after an order is placed, in the language the
 * order was placed in (orders.locale).
 */
class OrderConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items.product');
        $this->locale($this->order->locale ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('shop.contact_email'), config('app.name'))],
            subject: __('email.order.subject', ['number' => $this->order->number], $this->locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmation',
            text: 'emails.orders.confirmation-text',
            with: [
                'order' => $this->order,
                // Private link: opens the order with or without an account.
                'orderUrl' => $this->order->privateUrl($this->locale),
                'isRtl' => $this->locale === 'ar',
            ],
        );
    }
}
