<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'locale',
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

    protected $hidden = ['access_token'];

    protected static function booted(): void
    {
        // Secret for the private order link (guests have no account).
        static::creating(function (Order $order) {
            $order->access_token ??= Str::random(40);
        });
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Private link to this order that works without logging in
     * (sent in the confirmation email; used by guests).
     */
    public function privateUrl(?string $locale = null): string
    {
        return route('orders.view', [
            'locale' => $locale ?? $this->locale ?? app()->getLocale(),
            'order' => $this->id,
            'token' => $this->access_token,
        ]);
    }

    /**
     * Where the current visitor should see this order: their account page
     * when they own it while logged in, otherwise the private link.
     */
    public function customerUrl(): string
    {
        return $this->user_id !== null && auth()->id() === $this->user_id
            ? route('orders.show', ['locale' => app()->getLocale(), 'order' => $this->id])
            : $this->privateUrl(app()->getLocale());
    }

    /**
     * Customer-facing order number, e.g. "000123".
     */
    public function getNumberAttribute(): string
    {
        return str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

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
