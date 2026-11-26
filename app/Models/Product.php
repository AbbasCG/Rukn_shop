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
    ];

    protected $casts = [
        'specifications' => 'array',
    ];

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
