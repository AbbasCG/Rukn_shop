<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
    ];

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
     * Lowest and highest price over the variants (or the product price).
     *
     * @return array{0: float, 1: float}
     */
    public function priceRange(): array
    {
        if (! $this->hasVariants()) {
            return [(float) $this->price, (float) $this->price];
        }

        $prices = $this->variants->map(fn (ProductVariant $v) => (float) ($v->price ?? $this->price));

        return [$prices->min(), $prices->max()];
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
        return $this->hasMany(Reviews::class);
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
        return $this->hasMany(order_items::class, 'product_id');
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

        return Storage::url($normalizedPath);
    }
}
