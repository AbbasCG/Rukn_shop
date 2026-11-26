<?php

namespace App\Support;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * Moving an order's quantities in and out of stock (cancellations, expired
 * online payments, re-activations). Call inside a DB transaction.
 */
class OrderStock
{
    /** Put the order's items back on the shelf. */
    public static function release(Order $order): void
    {
        self::adjust($order->loadMissing('items'), +1);
    }

    /**
     * Take the order's items out of stock again.
     *
     * @throws InsufficientStockException
     */
    public static function take(Order $order): void
    {
        self::adjust($order->loadMissing('items'), -1);
    }

    /**
     * Put an order's quantities back into stock (+1) or take them out again (-1).
     * Works per variant for products sold in sizes/colours (and keeps
     * products.stock, their sum, in step). Rows are locked; taking stock fails
     * if there is not enough left.
     */
    private static function adjust(Order $order, int $direction): void
    {
        // Lines whose product was deleted (product_id null) have no stock to adjust.
        $lines = $order->items->whereNotNull('product_id');

        $products = Product::whereIn('id', $lines->pluck('product_id')->unique())->lockForUpdate()->get()->keyBy('id');
        $variants = ProductVariant::whereIn('id', $lines->pluck('variant_id')->filter()->unique())->lockForUpdate()->get()->keyBy('id');

        // Check everything first, then change, so a refusal leaves nothing half-done.
        if ($direction < 0) {
            foreach ($lines->groupBy(fn ($l) => $l->product_id . ':' . ($l->variant_id ?? '')) as $group) {
                $line = $group->first();
                $quantity = (int) $group->sum('quantity');
                $variant = $line->variant_id ? $variants->get($line->variant_id) : null;
                $product = $products->get($line->product_id);
                $available = $variant ? $variant->stock : ($product?->stock ?? 0);

                if ($product && ($variant || ! $line->variant_id) && $available < $quantity) {
                    throw new InsufficientStockException(__('admin.orders.messages.not_enough_stock', [
                        'product' => $line->full_name,
                        'count' => max(0, $available),
                    ]));
                }
            }
        }

        foreach ($lines as $line) {
            $product = $products->get($line->product_id);
            if (! $product) {
                continue;
            }

            $variant = $line->variant_id ? $variants->get($line->variant_id) : null;
            $variant?->increment('stock', $direction * (int) $line->quantity);
            $product->increment('stock', $direction * (int) $line->quantity);
        }

        // Products sold in sizes/colours: products.stock must stay the sum of
        // their variants (also when a variant was deleted since the order).
        $products->each(fn (Product $product) => $product->syncStockFromVariants());
    }
}
