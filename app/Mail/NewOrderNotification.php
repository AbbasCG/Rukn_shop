<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Sent to store staff (config shop.order_notifications.emails) for every new
 * order. Reply-To is the customer, so staff can answer them directly.
 */
class NewOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items.product');
        $this->locale(config('shop.order_notifications.locale') ?: config('app.locale'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->order->email, $this->order->name)],
            subject: __('email.new_order.subject', [
                'number' => $this->order->number,
                'total' => $this->withLocale($this->locale, fn () => money($this->order->total)),
                'name' => $this->order->name,
            ], $this->locale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.new-order',
            text: 'emails.orders.new-order-text',
            with: [
                'order' => $this->order,
                'adminUrl' => route('admin.orders.show', ['locale' => $this->locale, 'order' => $this->order]),
                'lowStock' => $this->lowStockProducts(),
                'isRtl' => $this->locale === 'ar',
            ],
        );
    }

    /**
     * Products from this order whose stock is now at or below the threshold.
     */
    public function lowStockProducts(): Collection
    {
        $threshold = (int) config('shop.order_notifications.low_stock_threshold');

        return $this->order->items
            ->pluck('product')
            ->filter()
            ->unique('id')
            ->map(fn ($product) => $product->fresh())
            ->filter(fn ($product) => $product && $product->stock <= $threshold)
            ->values();
    }
}
