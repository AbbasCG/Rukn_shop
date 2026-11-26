<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An old product slug that redirects to the product's current URL.
 */
class ProductSlugRedirect extends Model
{
    protected $fillable = ['product_id', 'old_slug'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
