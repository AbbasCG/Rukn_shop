<?php

namespace App\Support;

/**
 * Shipping cost and the related policy figures, from config/shop.php.
 */
class Shipping
{
    public static function cost(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        return $subtotal >= self::freeThreshold() ? 0.0 : round((float) config('shop.shipping.flat_rate'), 2);
    }

    public static function freeThreshold(): float
    {
        return (float) config('shop.shipping.free_threshold');
    }

    /**
     * How much more the customer needs to spend for free shipping (0 when reached).
     */
    public static function remainingForFree(float $subtotal): float
    {
        return max(0.0, round(self::freeThreshold() - $subtotal, 2));
    }

    /**
     * Replacements for policy texts: :threshold, :rate, :min_days, :max_days, :return_days.
     */
    public static function textParams(): array
    {
        return [
            'threshold' => money(self::freeThreshold()),
            'rate' => money(config('shop.shipping.flat_rate')),
            'min_days' => config('shop.shipping.delivery_days_min'),
            'max_days' => config('shop.shipping.delivery_days_max'),
            'return_days' => config('shop.returns.days'),
        ];
    }
}
