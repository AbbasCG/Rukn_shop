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
use Illuminate\Support\Collection;

/**
 * Sent to store staff (config shop.order_notifications.emails) for every new
 * order. Reply-To is the customer, so staff can answer them directly.
 */
class NewOrderNotification extends Mailable implements ShouldQueue
{
    use Queueable, SentInBackground, SerializesModels;

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
     * What this order brought down to the threshold or below: per variant for
     * products sold in sizes/colours, else per product.
     *
     * @return Collection<int, array{name: string, stock: int}>
     */
    public function lowStockProducts(): Collection
    {
        $threshold = (int) config('shop.order_notifications.low_stock_threshold');

        return $this->order->items
            ->filter(fn ($item) => $item->product_id !== null)
            ->unique(fn ($item) => $item->product_id . ':' . $item->variant_id)
            ->map(function ($item) {
                $variant = $item->variant_id ? \App\Models\ProductVariant::with('product')->find($item->variant_id) : null;
                $product = $variant?->product ?? \App\Models\Product::find($item->product_id);
                if (! $product) {
                    return null;
                }

                return [
                    'name' => $product->name . ($variant ? ' (' . $variant->label() . ')' : ''),
                    'stock' => (int) ($variant ? $variant->stock : $product->stock),
                ];
            })
            ->filter(fn ($entry) => $entry !== null && $entry['stock'] <= $threshold)
            ->values();
    }
}
