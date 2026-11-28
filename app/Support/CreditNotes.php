<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\ProductVariant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Returns and credit notes (creditfacturen).
 *
 * A return lists the units sent back; its credit note refunds what was paid
 * for them (and, optionally, the original shipping), with the VAT rate of
 * the order. It takes the next number of the invoice sequence and refers to
 * the order's invoice. Refunds themselves are made by hand (Mollie
 * dashboard or bank transfer) and then marked as done.
 */
class CreditNotes
{
    /** Orders whose goods were delivered or paid for can be returned. */
    public static function canReturn(Order $order): bool
    {
        return $order->status !== 'cancelled'
            && (Invoices::isDue($order) || $order->hasInvoice())
            && self::returnableItems($order)->isNotEmpty();
    }

    /** Lines with units not returned yet: [order_item_id => units left]. */
    public static function returnableItems(Order $order): \Illuminate\Support\Collection
    {
        return $order->items()->withSum('returnItems as returned', 'quantity')->get()
            ->mapWithKeys(fn (OrderItem $item) => [$item->id => (int) $item->quantity - (int) $item->returned])
            ->filter(fn ($left) => $left > 0);
    }

    /** Shipping that can still be refunded (once per order). */
    public static function refundableShipping(Order $order): float
    {
        $refunded = (float) $order->returns()->sum('shipping_amount');

        return max(0.0, round((float) $order->shipping_cost - $refunded, 2));
    }

    /**
     * Register a return and create its credit note.
     *
     * @param  array<int, int>  $quantities  order_item_id => units returned
     */
    public static function create(Order $order, array $quantities, bool $refundShipping, bool $restock, ?string $reason, bool $refunded, ?int $userId = null): OrderReturn
    {
        return DB::transaction(function () use ($order, $quantities, $refundShipping, $restock, $reason, $refunded, $userId) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $left = self::returnableItems($order);
            $items = $order->items()->get()->keyBy('id');

            $lines = [];
            foreach ($quantities as $itemId => $quantity) {
                $quantity = (int) $quantity;
                if ($quantity <= 0) {
                    continue;
                }
                if (! $items->has($itemId) || $quantity > (int) $left->get($itemId, 0)) {
                    throw ValidationException::withMessages(['quantities' => __('returns.errors.too_many')]);
                }
                $lines[] = ['item' => $items[$itemId], 'quantity' => $quantity];
            }

            $shipping = $refundShipping ? self::refundableShipping($order) : 0.0;
            if (! $lines && $shipping <= 0) {
                throw ValidationException::withMessages(['quantities' => __('returns.errors.nothing')]);
            }

            // The credit note refers to the invoice: make sure there is one.
            Invoices::assign($order);
            $order->refresh();
            if (! $order->hasInvoice()) {
                throw ValidationException::withMessages(['quantities' => __('returns.errors.not_invoiced')]);
            }

            $itemsAmount = round(collect($lines)->sum(fn ($l) => (float) $l['item']->price * $l['quantity']), 2);
            $total = round($itemsAmount + $shipping, 2);
            $rate = $order->vat_rate !== null ? (float) $order->vat_rate : Vat::rate();

            $return = OrderReturn::create([
                'order_id' => $order->id,
                'credit_note_number' => Invoices::nextNumber(),
                'items_amount' => $itemsAmount,
                'shipping_amount' => $shipping,
                'total' => $total,
                'vat_rate' => $rate,
                'vat_amount' => Vat::included($total, $rate),
                'restocked' => $restock,
                'reason' => filled($reason) ? trim($reason) : null,
                'refunded_at' => $refunded ? now() : null,
                'created_by' => $userId,
            ]);

            foreach ($lines as $line) {
                $return->items()->create([
                    'order_item_id' => $line['item']->id,
                    'quantity' => $line['quantity'],
                    'price' => $line['item']->price,
                ]);

                if ($restock) {
                    self::restock($line['item'], $line['quantity']);
                }
            }

            // Everything sent back: the order as a whole is refunded.
            if (self::returnableItems($order)->isEmpty()) {
                $order->forceFill(['status' => 'refunded'])->save();
            }

            return $return->load('items.orderItem', 'order');
        });
    }

    /** Put returned units back on sale (this also emails "back in stock"). */
    private static function restock(OrderItem $item, int $quantity): void
    {
        $variant = $item->variant_id ? ProductVariant::whereKey($item->variant_id)->lockForUpdate()->first() : null;
        $product = $item->product_id ? \App\Models\Product::whereKey($item->product_id)->lockForUpdate()->first() : null;

        if (! $product) {
            return;   // the product no longer exists
        }

        $variant?->increment('stock', $quantity);
        $product->increment('stock', $quantity);
    }

    public static function filename(OrderReturn $return): string
    {
        $dutch = Invoices::locale($return->order) === 'nl';

        return 'Rukn-' . ($dutch ? 'creditfactuur' : 'credit-note') . '-' . $return->credit_note_number . '.pdf';
    }

    /** The PDF as bytes, in the invoice language of the order. */
    public static function pdf(OrderReturn $return): string
    {
        $return->loadMissing('items.orderItem', 'order');
        $previous = app()->getLocale();
        app()->setLocale(Invoices::locale($return->order));

        try {
            return Pdf::loadView('invoices.credit-note', ['return' => $return, 'order' => $return->order, 'business' => config('shop.business')])
                ->setPaper('a4')
                ->output();
        } finally {
            app()->setLocale($previous);
        }
    }
}
