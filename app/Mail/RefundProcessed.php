<?php

namespace App\Mail;

use App\Mail\Concerns\SentInBackground;
use App\Models\OrderReturn;
use App\Support\CreditNotes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the customer their return has been refunded, with the credit note.
 */
class RefundProcessed extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

    public function __construct(public OrderReturn $return)
    {
        $this->locale($this->return->order->locale ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('shop.contact_email'), config('app.name'))],
            subject: __('email.refund.subject', ['number' => $this->return->order->number], $this->locale),
        );
    }

    public function content(): Content
    {
        $order = $this->return->order;

        return new Content(
            view: 'emails.orders.refund',
            text: 'emails.orders.refund-text',
            with: [
                'order' => $order,
                'return' => $this->return->loadMissing('items.orderItem'),
                'method' => $order->payment_method === 'mollie' ? __('email.refund.method_online') : __('email.refund.method_bank'),
                'orderUrl' => $order->customerUrl(),
                'isRtl' => $this->locale === 'ar',
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => CreditNotes::pdf($this->return), CreditNotes::filename($this->return))
                ->withMime('application/pdf'),
        ];
    }
}
