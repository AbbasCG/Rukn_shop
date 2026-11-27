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
 * "Your order is on its way", with the track & trace link when there is
 * one. Sent in the language the order was placed in.
 */
class OrderShipped extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

    /**
     * @param  bool  $trackingUpdate  true when only the tracking details changed
     */
    public function __construct(public Order $order, public bool $trackingUpdate = false)
    {
        $this->order->loadMissing('items.product');
        $this->locale($this->order->locale ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        $key = $this->trackingUpdate ? 'email.shipped.subject_update' : 'email.shipped.subject';

        return new Envelope(
            replyTo: [new Address(config('shop.contact_email'), config('app.name'))],
            subject: __($key, ['number' => $this->order->number], $this->locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.shipped',
            text: 'emails.orders.shipped-text',
            with: [
                'order' => $this->order,
                'trackingUrl' => $this->order->trackingUrl(),
                'orderUrl' => $this->order->privateUrl($this->locale),
                'isRtl' => $this->locale === 'ar',
                'trackingUpdate' => $this->trackingUpdate,
            ],
        );
    }
}
