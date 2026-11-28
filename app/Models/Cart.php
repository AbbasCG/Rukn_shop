<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;

class Cart extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
        'status',
        'price_at_time',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Current price of one unit (a running sale applied): the variant's
     * price, else the product's.
     */
    public function unitPrice(): float
    {
        if (! $this->product) {
            return 0.0;
        }

        return $this->variant
            ? $this->product->salePriceFor($this->variant)
            : $this->product->currentPrice();
    }

    /** Price of one unit before any reduction (shown crossed out). */
    public function regularUnitPrice(): float
    {
        if (! $this->product) {
            return 0.0;
        }

        return $this->variant ? (float) ($this->variant->price ?? $this->product->price) : (float) $this->product->price;
    }

    /**
     * Stock available for this line: the variant's stock, else the product's.
     */
    /**
     * What the cart routes use for this line: the saved id, or for a guest
     * the session key ("<productId>[:<variantId>]").
     */
    public function lineKey(): string
    {
        return $this->exists ? (string) $this->id : (string) $this->guest_key;
    }

    public function availableStock(): int
    {
        return (int) ($this->variant ? $this->variant->stock : ($this->product?->stock ?? 0));
    }

    /**
     * A line is purchasable when its product is public and, for products
     * sold in sizes/colours, a variant of that product is chosen.
     */
    public function isValid(): bool
    {
        if (! $this->product || ! $this->product->is_active) {
            return false;
        }

        return $this->product->hasVariants()
            ? $this->variant !== null && $this->variant->product_id === $this->product->id
            : $this->variant_id === null;
    }


}
