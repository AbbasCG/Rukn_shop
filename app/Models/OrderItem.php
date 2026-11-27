<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_label',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
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
     * Name plus the chosen size/colour as ordered: "Black Abaya (54 · Black)".
     */
    public function getFullNameAttribute(): string
    {
        return $this->display_name . ($this->variant_label ? " ({$this->variant_label})" : '');
    }

    /**
     * The name as it was when ordered; the product may since have been
     * renamed or deleted (product_id is then null).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->product_name
            ?? $this->product?->name
            ?? __('orders.items.product_unavailable');
    }

    
}
