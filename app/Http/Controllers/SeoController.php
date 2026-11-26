<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Every public page in every language, each listing its translations
     * (xhtml:link hreflang) so search engines connect the three versions.
     */
    public function sitemap(): Response
    {
        $locales = config('app.supported_locales', ['nl', 'en', 'ar']);
        $entries = [];

        $static = ['home', 'products.index', 'about', 'contact', 'faq', 'privacy-policy', 'terms-of-service'];
        foreach ($static as $route) {
            $entries[] = [
                'urls' => collect($locales)->mapWithKeys(fn ($l) => [$l => route($route, ['locale' => $l])])->all(),
                'lastmod' => null,
            ];
        }

        Category::where('is_active', true)->get()->each(function (Category $category) use (&$entries, $locales) {
            $entries[] = [
                'urls' => collect($locales)->mapWithKeys(fn ($l) => [$l => route('products.index', ['locale' => $l, 'category' => $category->slug])])->all(),
                'lastmod' => $category->updated_at,
            ];
        });

        Product::where('is_active', true)->orderBy('id')->get(['id', 'slug', 'updated_at'])
            ->each(function (Product $product) use (&$entries, $locales) {
                $entries[] = [
                    'urls' => collect($locales)->mapWithKeys(fn ($l) => [$l => route('products.show', ['locale' => $l, 'product' => $product])])->all(),
                    'lastmod' => $product->updated_at,
                ];
            });

        return response()
            ->view('seo.sitemap', ['entries' => $entries], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            // Private or per-visitor pages, in every language.
            'Disallow: /*/admin',
            'Disallow: /*/cart',
            'Disallow: /*/checkout',
            'Disallow: /*/orders',
            'Disallow: /*/profile',
            'Disallow: /*/login',
            'Disallow: /*/register',
            'Disallow: /*/forgot-password',
            'Disallow: /*/reset-password',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
