<?php

namespace App\Support;

/**
 * Consumer prices include VAT (btw). This works out the VAT part of an
 * amount that already includes it, at the shop's rate (config shop.vat).
 */
class Vat
{
    public static function rate(): float
    {
        return (float) config('shop.vat.rate', 21);
    }

    /** "21" for 21.0, "9" for 9.0, "5.5" for 5.5. */
    public static function rateLabel(?float $rate = null): string
    {
        $rate ??= self::rate();

        return rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
    }

    /** VAT contained in a VAT-inclusive amount: 121.00 at 21% → 21.00. */
    public static function included(float $amountInclVat, ?float $rate = null): float
    {
        $rate ??= self::rate();

        return $rate > 0 ? round($amountInclVat * $rate / (100 + $rate), 2) : 0.0;
    }
}
