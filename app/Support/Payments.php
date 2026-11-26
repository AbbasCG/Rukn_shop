<?php

namespace App\Support;

/**
 * Which payment methods the shop offers right now.
 */
class Payments
{
    /** Online payment via Mollie is offered once a Mollie API key is set. */
    public static function onlineEnabled(): bool
    {
        return filled(config('services.mollie.key'));
    }

    public static function codEnabled(): bool
    {
        return (bool) config('shop.payments.cod_enabled', true);
    }

    /**
     * Method codes for the checkout, online first.
     *
     * @return list<string>
     */
    public static function methods(): array
    {
        $methods = [];
        if (self::onlineEnabled()) {
            $methods[] = 'mollie';
        }
        if (self::codEnabled() || $methods === []) {
            $methods[] = 'cod'; // never leave the checkout without a way to pay
        }

        return $methods;
    }

    /**
     * Translation key for texts that describe how to pay: "<key>" when only
     * pay-on-delivery is offered, "<key>_online" for online only, "<key>_both".
     */
    public static function textKey(string $key): string
    {
        $methods = self::methods();

        return match (true) {
            $methods === ['cod'] => $key,
            $methods === ['mollie'] => $key . '_online',
            default => $key . '_both',
        };
    }
}
