<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * A customer's request to be emailed once a sold-out product (or size/colour)
 * is in stock again. Sent once; then removed after a while (see prunable()).
 */
class StockNotification extends Model
{
    use Prunable;

    /** Requests that were never answered are removed after this many days. */
    public const KEEP_PENDING_DAYS = 180;

    /** Answered requests are removed after this many days. */
    public const KEEP_SENT_DAYS = 30;

    protected $fillable = ['product_id', 'variant_id', 'user_id', 'email', 'locale', 'notified_at'];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('notified_at');
    }

    /** Old requests: kept only as long as needed (privacy policy). */
    public function prunable(): Builder
    {
        return static::query()->where(function (Builder $q) {
            $q->whereNull('notified_at')->where('created_at', '<', now()->subDays(self::KEEP_PENDING_DAYS))
                ->orWhere('notified_at', '<', now()->subDays(self::KEEP_SENT_DAYS));
        });
    }
}
