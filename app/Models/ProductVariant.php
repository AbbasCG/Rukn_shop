<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One size/colour combination of a product, with its own stock and an
 * optional price (null = the product price).
 */
class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size', 'color_key', 'price', 'sale_price', 'stock'];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected static function booted(): void
    {
        // A sold-out size/colour is back: email the customers who asked.
        static::updated(function (ProductVariant $variant) {
            if ($variant->wasChanged('stock') && (int) $variant->stock > 0
                && (int) $variant->stock > (int) $variant->getOriginal('stock')) {
                \App\Support\StockAlerts::sendFor($variant->product);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Price before any reduction: the variant's own price, else the product's.
     */
    public function regularPrice(): float
    {
        return (float) ($this->price ?? $this->product->price);
    }

    /**
     * Price the customer pays now (a running sale applied).
     */
    public function effectivePrice(): float
    {
        return $this->product->salePriceFor($this);
    }

    public function isOnSale(): bool
    {
        return $this->effectivePrice() < $this->regularPrice();
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
