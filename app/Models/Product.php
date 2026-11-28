<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\Media;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    public const FALLBACK_IMAGE = 'images/placeholder.svg';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'long_description',
        'specifications',
        'price',
        'sale_type',
        'sale_value',
        'sale_starts_at',
        'sale_ends_at',
        'stock',
        'category_id',
        'image_url',
        'is_active',
        'size_system',
        'sizes',
        'colors',
    ];

    protected $casts = [
        'specifications' => 'array',
        'sizes' => 'array',
        'colors' => 'array',
        'sale_value' => 'decimal:2',
        'sale_starts_at' => 'datetime',
        'sale_ends_at' => 'datetime',
        'regular_price_raised_at' => 'datetime',
        'last_sale_ended_at' => 'datetime',
    ];

    /** Kinds of price reduction: a percentage off, or a new (lower) price. */
    public const SALE_TYPES = ['percent', 'price'];

    /** EU/Dutch rule: a "was" price is the lowest price of the 30 days before. */
    public const SALE_REFERENCE_DAYS = 30;

    /** Sale dates are whole days in the shop's own time zone. */
    public const SALE_TIMEZONE = 'Europe/Amsterdam';

    /** Ways sizes can be written; chosen per product in the admin. */
    public const SIZE_SYSTEMS = ['lengths', 'letters', 'custom'];

    /** Abaya lengths (inches) offered when the size system is "lengths". */
    public const SIZE_LENGTHS = ['50', '52', '54', '56', '58', '60', '62'];

    /** Letter sizes offered when the size system is "letters". */
    public const SIZE_LETTERS = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    protected static function booted(): void
    {
        // Remember the previous slug so its links keep redirecting here.
        static::updated(function (Product $product) {
            $previous = (string) $product->getOriginal('slug');

            if ($product->wasChanged('slug') && $previous !== '') {
                ProductSlugRedirect::updateOrCreate(
                    ['old_slug' => $previous],
                    ['product_id' => $product->id],
                );
            }
        });

        // A slug in use belongs to its product: drop any redirect claiming it
        // (e.g. a product taking a slug another product used before).
        static::saved(function (Product $product) {
            ProductSlugRedirect::where('old_slug', $product->slug)->delete();
        });
    }

    /**
     * Best sellers first: units ordered, cancelled orders not counted.
     * A correlated subquery, so it works the same on SQLite, MySQL and
     * Postgres (and products never ordered count as 0, not NULL).
     */
    public function scopeOrderByUnitsSold($query)
    {
        return $query->orderByDesc(
            OrderItem::query()
                ->selectRaw('coalesce(sum(order_items.quantity), 0)')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', '!=', 'cancelled')
                ->whereColumn('order_items.product_id', 'products.id')
        );
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }

    /**
     * True when the product is sold in sizes and/or colours.
     */
    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    public function colorDefinition(string $key): ?array
    {
        return collect($this->colors ?? [])->firstWhere('key', $key);
    }

    /**
     * Colour name in the active (or given) locale, falling back to another
     * language and finally to the key.
     */
    public function colorName(string $key, ?string $locale = null): string
    {
        $names = $this->colorDefinition($key)['names'] ?? [];
        $locale ??= app()->getLocale();

        return $names[$locale] ?? collect($names)->filter()->first() ?? $key;
    }

    /**
     * Lowest and highest price the customer pays now (sales applied), over
     * the variants (or the product price).
     *
     * @return array{0: float, 1: float}
     */
    public function priceRange(): array
    {
        if (! $this->hasVariants()) {
            $price = $this->currentPrice();

            return [$price, $price];
        }

        $prices = $this->variants->map(fn (ProductVariant $v) => $v->setRelation('product', $this)->effectivePrice());

        return [$prices->min(), $prices->max()];
    }

    /**
     * Same as priceRange(), before reductions (the crossed-out price).
     *
     * @return array{0: float, 1: float}
     */
    public function regularPriceRange(): array
    {
        if (! $this->hasVariants()) {
            return [(float) $this->price, (float) $this->price];
        }

        $prices = $this->variants->map(fn (ProductVariant $v) => (float) ($v->price ?? $this->price));

        return [$prices->min(), $prices->max()];
    }

    // ---- Price reductions ----

    /**
     * A reduction is set and today falls between its (optional) dates.
     */
    public function saleIsRunning(?\DateTimeInterface $at = null): bool
    {
        if (! in_array($this->sale_type, self::SALE_TYPES, true) || $this->sale_value === null) {
            return false;
        }

        $at = $at ? \Illuminate\Support\Carbon::instance($at) : now();

        return ($this->sale_starts_at === null || $this->sale_starts_at->lte($at))
            && ($this->sale_ends_at === null || $this->sale_ends_at->gt($at));
    }

    /** Set, but its start date is still to come. */
    public function saleIsScheduled(): bool
    {
        return in_array($this->sale_type, self::SALE_TYPES, true) && $this->sale_value !== null
            && $this->sale_starts_at !== null && $this->sale_starts_at->isFuture()
            && ($this->sale_ends_at === null || $this->sale_ends_at->gt($this->sale_starts_at));
    }

    /**
     * What one unit costs now: the product, or one of its variants.
     *
     * - percent: every size/colour gets the same % off its own price.
     * - price:   the reduced price replaces the product price; a variant
     *            with its own price uses its own reduced price (none = no
     *            reduction for that variant).
     *
     * Never more than the regular price, never below zero.
     */
    public function salePriceFor(?ProductVariant $variant = null): float
    {
        $regular = $variant ? (float) ($variant->price ?? $this->price) : (float) $this->price;

        if (! $this->saleIsRunning()) {
            return $regular;
        }

        $value = (float) $this->sale_value;

        $reduced = match ($this->sale_type) {
            'percent' => round($regular * (100 - min(100, max(0, $value))) / 100, 2),
            'price' => $variant && $variant->price !== null
                ? ($variant->sale_price !== null ? (float) $variant->sale_price : $regular)
                : $value,
        };

        return max(0.0, min($regular, $reduced));
    }

    /** Last day of the sale (inclusive), in the shop's time zone, or null. */
    public function saleLastDay(): ?\Illuminate\Support\Carbon
    {
        return $this->sale_ends_at?->copy()->tz(self::SALE_TIMEZONE)->subSecond()->startOfDay();
    }

    /** First day of the sale, in the shop's time zone, or null. */
    public function saleFirstDay(): ?\Illuminate\Support\Carbon
    {
        return $this->sale_starts_at?->copy()->tz(self::SALE_TIMEZONE)->startOfDay();
    }

    /** Price of the product itself now (products without variants). */
    public function currentPrice(): float
    {
        return $this->salePriceFor();
    }

    /**
     * Biggest reduction in whole percent (for the badge), 0 when none.
     * $upTo is true when sizes/colours are reduced by different amounts.
     *
     * @return array{percent: int, upTo: bool}
     */
    public function discount(): array
    {
        if (! $this->saleIsRunning()) {
            return ['percent' => 0, 'upTo' => false];
        }

        $pairs = $this->hasVariants()
            ? $this->variants->map(fn (ProductVariant $v) => [$v->setRelation('product', $this)->regularPrice(), $v->effectivePrice()])
            : collect([[(float) $this->price, $this->currentPrice()]]);

        $percents = $pairs->map(fn ($p) => $p[0] > 0 ? (int) floor(round((1 - $p[1] / $p[0]) * 100, 4)) : 0)->unique()->values();
        $max = (int) $percents->max();

        return ['percent' => max(0, $max), 'upTo' => $max > 0 && $percents->count() > 1];
    }

    /**
     * SQL for the price a product (without variant prices) costs at $at, for
     * filtering and sorting listings. Works on SQLite, MySQL and Postgres.
     *
     * @return array{0: string, 1: array}
     */
    public static function currentPriceSql(?\DateTimeInterface $at = null): array
    {
        $now = ($at ? \Illuminate\Support\Carbon::instance($at) : now())->format('Y-m-d H:i:s');
        $running = "products.sale_value is not null"
            . " and (products.sale_starts_at is null or products.sale_starts_at <= ?)"
            . " and (products.sale_ends_at is null or products.sale_ends_at > ?)";

        $sql = "(case"
            . " when products.sale_type = 'percent' and {$running} then round(products.price * (100 - products.sale_value) / 100, 2)"
            . " when products.sale_type = 'price' and {$running} and products.sale_value < products.price then products.sale_value"
            . " else products.price end)";

        return [$sql, [$now, $now, $now, $now]];
    }

    /**
     * Reasons the crossed-out price may not be the lowest price of the 30
     * days before the sale starts (Dutch/EU price rules), for the admin.
     *
     * @return list<array{key: string, date: \Illuminate\Support\Carbon}>
     */
    public function saleReferenceWarnings(): array
    {
        if (! in_array($this->sale_type, self::SALE_TYPES, true)) {
            return [];
        }

        $start = $this->sale_starts_at ?? $this->updated_at ?? now();
        $since = $start->copy()->subDays(self::SALE_REFERENCE_DAYS);
        $warnings = [];

        if ($this->regular_price_raised_at && $this->regular_price_raised_at->between($since, $start)) {
            $warnings[] = ['key' => 'price_raised', 'date' => $this->regular_price_raised_at];
        }
        if ($this->last_sale_ended_at && $this->last_sale_ended_at->between($since, $start)) {
            $warnings[] = ['key' => 'recent_sale', 'date' => $this->last_sale_ended_at];
        }

        return $warnings;
    }

    /**
     * For products with variants, products.stock is the sum of their stock
     * (used by listings, low-stock signals and the sitemap).
     */
    public function syncStockFromVariants(): void
    {
        if ($this->variants()->exists()) {
            $this->forceFill(['stock' => (int) $this->variants()->sum('stock')])->saveQuietly();
        }
    }

    public function slugRedirects()
    {
        return $this->hasMany(ProductSlugRedirect::class);
    }

    /**
     * Public URLs use the slug: /nl/products/black-abaya.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Resolve by slug, then by the numeric id (/nl/products/12), then by a
     * previous slug the product used. The storefront redirects every
     * non-canonical match permanently to the current slug URL.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        $value = (string) $value;

        return $this->where('slug', $value)->first()
            ?? ($value !== Str::lower($value) ? $this->where('slug', Str::lower($value))->first() : null)
            ?? (ctype_digit($value) ? $this->whereKey((int) $value)->first() : null)
            ?? ProductSlugRedirect::where('old_slug', Str::lower($value))->first()?->product;
    }

    /**
     * A URL-safe slug for $name that no other product uses: "black-abaya",
     * then "black-abaya-2", "black-abaya-3", …
     */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;

        for ($i = 2; static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** Reviews shown in the shop: approved, newest first. */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->approved()->latest('approved_at')->latest('id');
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the primary image for the product.
     */
    public function primaryImage()
    {
        // Uses the eager-loaded relation when present (avoids one query per card).
        $images = $this->images;

        return $images->firstWhere('is_primary', true) ?? $images->first();
    }

    /**
     * Get the resolved URL of the product primary image with fallback.
     */
    public function primaryImageUrl(): string
    {
        $primaryImagePath = optional($this->primaryImage())->path;

        return $this->resolveImageUrl($primaryImagePath)
            ?? $this->resolveImageUrl($this->image_url)
            ?? asset(self::FALLBACK_IMAGE);
    }

    /**
     * Resolve any image path/URL to a displayable URL with fallback.
     */
    public function imageUrlFromPath(?string $path): string
    {
        return $this->resolveImageUrl($path) ?? asset(self::FALLBACK_IMAGE);
    }

    /**
     * Resolve product legacy image_url value to URL or null.
     */
    public function legacyImageUrl(): ?string
    {
        return $this->resolveImageUrl($this->image_url);
    }

    /**
     * Convert local storage/public paths and external URLs to valid image URLs.
     */
    private function resolveImageUrl(?string $path): ?string
    {
        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (Str::startsWith($normalizedPath, ['storage/', 'images/'])) {
            return asset($normalizedPath);
        }

        return Media::url($normalizedPath);
    }
}
