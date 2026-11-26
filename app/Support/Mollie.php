<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Minimal Mollie Payments API client (https://docs.mollie.com/reference/v2).
 * Laravel's HTTP client keeps this dependency-free and easy to fake in tests.
 */
class Mollie
{
    /**
     * Create a payment. Returns the Mollie payment (id, status, _links.checkout.href…).
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public static function createPayment(array $payload): array
    {
        return self::client()->post('/payments', $payload)->throw()->json();
    }

    /**
     * The current state of a payment, straight from Mollie.
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public static function getPayment(string $id): array
    {
        return self::client()->get('/payments/' . rawurlencode($id))->throw()->json();
    }

    /** Mollie has no Arabic checkout; Arabic visitors get English. */
    public static function locale(?string $locale): string
    {
        return match ($locale) {
            'nl' => 'nl_NL',
            default => 'en_GB',
        };
    }

    private static function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.mollie.api_url'), '/'))
            ->withToken((string) config('services.mollie.key'))
            ->acceptJson()
            ->timeout(15);
    }
}
