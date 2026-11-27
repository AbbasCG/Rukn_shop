<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/**
 * The cart of a visitor who is not logged in, kept in the session as
 * cart.items = ["<productId>" or "<productId>:<variantId>" => quantity].
 * (Plain product ids are what older sessions contain.)
 */
class GuestCart
{
    public static function key(int $productId, ?int $variantId): string
    {
        return $variantId ? "{$productId}:{$variantId}" : (string) $productId;
    }

    /**
     * @return array{0: int, 1: int|null}
     */
    public static function parseKey(string|int $key): array
    {
        $parts = explode(':', (string) $key, 2);

        return [(int) $parts[0], isset($parts[1]) ? (int) $parts[1] : null];
    }

    /** @return array<string, int> */
    public static function raw(): array
    {
        return session('cart.items', []);
    }

    public static function put(array $items): void
    {
        $items = array_filter($items, fn ($qty) => (int) $qty > 0);
        session(['cart.items' => $items, 'cart.count' => array_sum($items)]);
    }

    /**
     * Unsaved Cart models (same API as a logged-in cart: product, variant,
     * quantity, unitPrice()…), skipping products that no longer exist.
     */
    public static function lines(): Collection
    {
        $raw = collect(self::raw());
        $keys = $raw->keys()->map(fn ($key) => self::parseKey($key));

        $products = Product::with('variants')->whereIn('id', $keys->pluck(0))->get()->keyBy('id');
        $variants = ProductVariant::whereIn('id', $keys->pluck(1)->filter())->get()->keyBy('id');

        return $raw->map(function ($qty, $key) use ($products, $variants) {
            [$productId, $variantId] = self::parseKey($key);
            $product = $products->get($productId);
            if (! $product) {
                return null;
            }

            $line = new Cart(['product_id' => $productId, 'variant_id' => $variantId, 'quantity' => (int) $qty]);
            $line->setRelation('product', $product);
            $line->setRelation('variant', $variantId ? $variants->get($variantId)?->setRelation('product', $product) : null);
            $line->guest_key = (string) $key;

            return $line;
        })->filter()->values();
    }
}
