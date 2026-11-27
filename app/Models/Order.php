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
        'tracking_carrier', 'tracking_number', 'tracking_url', 'shipped_at',
        'delivered_at', 'review_requested_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'review_requested_at' => 'datetime',
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
        return $this->hasMany(OrderItem::class);
    }

    // Alias for items() for convenience
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Allowed status values
     */
    public static function statuses(): array
    {
        return ['pending','paid','processing','shipped','delivered','cancelled','refunded'];
    }

    public function hasTracking(): bool
    {
        return filled($this->tracking_number);
    }

    /**
     * Carrier name for display ("PostNL"), or null for "other"/none.
     */
    public function carrierName(): ?string
    {
        return config("shop.carriers.{$this->tracking_carrier}.name");
    }

    /**
     * Link to the carrier's track & trace page: the custom link when one was
     * entered, else built from the carrier's template, else null.
     */
    public function trackingUrl(): ?string
    {
        if (filled($this->tracking_url)) {
            return $this->tracking_url;
        }

        $template = config("shop.carriers.{$this->tracking_carrier}.url");
        if (! $this->hasTracking() || ! $template) {
            return null;
        }

        return strtr($template, [
            '{code}' => rawurlencode(preg_replace('/\s+/', '', $this->tracking_number)),
            '{country}' => rawurlencode($this->countryCode()),
            '{postcode}' => rawurlencode(strtoupper(preg_replace('/\s+/', '', (string) $this->postal_code))),
        ]);
    }

    /**
     * Two-letter code for the delivery country ("NL" for "Nederland").
     * Checkout stores the country as typed, so common spellings are mapped.
     */
    public function countryCode(): string
    {
        $country = mb_strtolower(trim((string) $this->country));

        if (preg_match('/^[a-z]{2}$/', $country)) {
            return strtoupper($country);
        }

        $names = [
            'NL' => ['nederland', 'netherlands', 'the netherlands', 'holland', 'هولندا'],
            'BE' => ['belgië', 'belgie', 'belgium', 'belgique', 'بلجيكا'],
            'DE' => ['duitsland', 'germany', 'deutschland', 'ألمانيا', 'المانيا'],
            'FR' => ['frankrijk', 'france', 'فرنسا'],
        ];

        foreach ($names as $code => $spellings) {
            if (in_array($country, $spellings, true)) {
                return $code;
            }
        }

        return 'NL';
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** Customers can review what they bought once the order has been delivered. */
    public function canBeReviewed(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Products of this order that can still be reviewed: still in the shop
     * and not reviewed from this order yet (one review per product).
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Product>
     */
    public function reviewableProducts(): \Illuminate\Support\Collection
    {
        $this->loadMissing(['items.product', 'reviews']);
        $reviewed = $this->reviews->pluck('product_id')->all();

        return $this->items->pluck('product')->filter()
            ->unique('id')
            ->reject(fn (Product $product) => in_array($product->id, $reviewed, true))
            ->values();
    }

    /** Private link to the review page of this order (works without an account). */
    public function reviewUrl(?string $locale = null): string
    {
        return route('reviews.create', [
            'locale' => $locale ?? $this->locale ?? app()->getLocale(),
            'order' => $this->id,
            'token' => $this->access_token,
        ]);
    }
}
