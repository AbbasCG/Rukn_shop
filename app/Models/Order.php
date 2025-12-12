<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'user_id',
        'status',
        'payment_status',
        'paid_at',
        'payment_method',
        'payment_reference',
        'name', 'email', 'phone',
        'address_line1', 'address_line2', 'postal_code', 'city', 'country',
        'subtotal', 'shipping_cost', 'total',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(order_items::class);
    }

    // Alias for items() for convenience
    public function items()
    {
        return $this->hasMany(order_items::class);
    }

    /**
     * Allowed status values
     */
    public static function statuses(): array
    {
        return ['pending','paid','processing','shipped','delivered','cancelled','refunded'];
    }
}
