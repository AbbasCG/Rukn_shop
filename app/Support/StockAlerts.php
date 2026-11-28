<?php

namespace App\Support;

use App\Mail\BackInStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * "Email me when it is back": sends the waiting requests of a product whose
 * product (or chosen size/colour) is in stock again. Called whenever stock
 * or visibility changes (admin edits, cancelled orders, ...); requests that
 * were already answered are skipped, so calling it twice is harmless.
 */
class StockAlerts
{
    public static function sendFor(Product $product): int
    {
        if (! $product->is_active) {
            return 0;
        }

        $pending = StockNotification::pending()->where('product_id', $product->id)->get();
        if ($pending->isEmpty()) {
            return 0;
        }

        $variants = ProductVariant::whereIn('id', $pending->pluck('variant_id')->filter())->get()->keyBy('id');
        $hasVariants = $product->variants()->exists();
        $sent = 0;

        foreach ($pending as $request) {
            $variant = $request->variant_id ? $variants->get($request->variant_id) : null;

            // The request must still match how the product is sold, and be in stock.
            $inStock = $request->variant_id
                ? $variant && $variant->stock > 0
                : ! $hasVariants && $product->stock > 0;
            if (! $inStock) {
                continue;
            }

            $variant?->setRelation('product', $product);

            // Marked first: never two emails for one request, even if the
            // mail is retried by the queue.
            $request->forceFill(['notified_at' => now()])->save();

            try {
                Mail::to($request->email)->queue(new BackInStock($product, $variant, $request->locale));
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Back-in-stock email could not be queued.', ['request_id' => $request->id, 'error' => $e->getMessage()]);
            }
        }

        return $sent;
    }

    /** Waiting requests per product (for the admin). */
    public static function waitingCount(Product $product): int
    {
        return StockNotification::pending()->where('product_id', $product->id)->count();
    }
}
