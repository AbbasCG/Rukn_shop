<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A discount code. Kind for now: free shipping, optionally only from a
 * minimum order amount (products subtotal, VAT included).
 */
class Coupon extends Model
{
    public const TYPES = ['free_shipping'];

    protected $fillable = ['code', 'type', 'min_order_amount', 'is_active', 'note'];

    protected $casts = [
        'min_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'uses_count' => 'integer',
    ];

    /** Codes are case-insensitive and ignore spaces: " gratis " → "GRATIS". */
    public static function normalize(?string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $code));
    }

    public static function findByCode(?string $code): ?self
    {
        $code = self::normalize($code);

        return $code === '' ? null : self::where('code', $code)->first();
    }

    public function setCodeAttribute(?string $value): void
    {
        $this->attributes['code'] = self::normalize($value);
    }

    /** Whether the order subtotal is high enough for this code. */
    public function meetsMinimum(float $subtotal): bool
    {
        return $this->min_order_amount === null || $subtotal >= (float) $this->min_order_amount;
    }
}
