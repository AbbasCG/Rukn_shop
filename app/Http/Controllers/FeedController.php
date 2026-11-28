<?php

namespace App\Http\Controllers;

use App\Support\ProductFeed;

/**
 * Product feed for Google Merchant Center and Meta (Instagram) catalogues.
 */
class FeedController extends Controller
{
    public function products(string $locale)
    {
        return response(ProductFeed::xml($locale), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            // The platforms fetch it a few times a day; a short cache is plenty.
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
