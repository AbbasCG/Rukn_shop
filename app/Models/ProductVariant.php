<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One size/colour combination of a product, with its own stock and an
 * optional price (null = the product price).
 */
class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size', 'color_key', 'price', 'stock'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Price the customer pays for this variant.
     */
    public function effectivePrice(): float
    {
        return (float) ($this->price ?? $this->product->price);
    }

    /**
     * Colour definition from the product ({key, hex, names}).
     */
    public function color(): ?array
    {
        return $this->color_key === null ? null : $this->product->colorDefinition($this->color_key);
    }

    /**
     * "54 · Black" in the active (or given) locale.
     */
    public function label(?string $locale = null): string
    {
        $parts = array_filter([
            $this->size,
            $this->color_key === null ? null : $this->product->colorName($this->color_key, $locale),
        ], fn ($part) => $part !== null && $part !== '');

        return implode(' · ', $parts);
    }
}
