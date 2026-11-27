<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A product review by a verified buyer. New reviews are "pending" until the
 * shop approves them; only approved reviews are shown in the shop.
 */
class Review extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $table = 'reviews';

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'author_name',
        'rating',
        'comment',
        'status',
        'locale',
        'approved_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /** Written from an order, so the reviewer really bought the product. */
    public function isVerified(): bool
    {
        return $this->order_id !== null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
