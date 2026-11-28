<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource (public).
     */
    public function index()
    {
        $query = Product::where('is_active', true)->with(['category', 'images', 'variants']);

        // Search
        $search = trim((string) request('q', ''));
        if ($search !== '') {
            // Case-insensitive on every driver (Postgres LIKE is case-sensitive),
            // with "%" and "_" in the query matched literally. "!" is used as the
            // escape character because a backslash is parsed differently by MySQL.
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($search));
            $like = '%' . $escaped . '%';

            // Grouped so the OR conditions cannot bypass the is_active filter.
            $query->where(function ($q) use ($like) {
                foreach (['name', 'short_description', 'long_description'] as $column) {
                    $q->orWhereRaw("LOWER({$column}) LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }

        // Category filter
        if (request('category')) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', request('category'));
            });
        }

        // Size / colour: something in stock in the chosen size(s) and colour(s).
        $selectedSizes = array_slice(array_filter((array) request('sizes', []), 'is_string'), 0, 20);
        $selectedColors = array_slice(array_filter((array) request('colors', []), 'is_string'), 0, 20);
        \App\Support\ProductFilters::apply($query, $selectedSizes, $selectedColors);

        // Price filter and sort use the price customers pay now (a running
        // reduction applied). min/max_price are numbers only.
        [$priceSql, $priceBindings] = Product::currentPriceSql();
        // The bound amount is cast: drivers may send it as text, and SQLite
        // orders every number before any text.
        $between = function (?float $min, ?float $max) use ($query, $priceSql, $priceBindings) {
            if ($min !== null) {
                $query->whereRaw("{$priceSql} >= cast(? as decimal(10,2))", [...$priceBindings, $min]);
            }
            if ($max !== null) {
                $query->whereRaw("{$priceSql} <= cast(? as decimal(10,2))", [...$priceBindings, $max]);
            }
        };

        if (request('price_range')) {
            match (request('price_range')) {
                '0-25' => $between(null, 25),
                '25-50' => $between(25, 50),
                '50-100' => $between(50, 100),
                '100+' => $between(100, null),
                default => null,
            };
        } else {
            $between(
                is_numeric(request('min_price')) ? (float) request('min_price') : null,
                is_numeric(request('max_price')) ? (float) request('max_price') : null,
            );
        }

        // Sorting
        $sort = request('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderByRaw("{$priceSql} asc", $priceBindings);
                break;
            case 'price_high':
                $query->orderByRaw("{$priceSql} desc", $priceBindings);
                break;
            case 'popular':
                // Real best sellers (it used to be a random order).
                $query->orderByUnitsSold()->latest();
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
            default:
                $query->latest();
        }

        $products = $query->paginate(8)->withQueryString();
        $categories = Category::where('is_active', true)->get();
        $sizeOptions = \App\Support\ProductFilters::sizeOptions();
        $colorOptions = \App\Support\ProductFilters::colorOptions();

        return view('products.index', compact('products', 'categories', 'sizeOptions', 'colorOptions', 'selectedSizes', 'selectedColors'));
    }

    /**
     * Display the specified resource (public).
     */
    public function show(Request $request, string $locale, Product $product)
    {
        // Hidden products are not public; admins can still preview them.
        if (! $product->is_active && $request->user()?->role !== 'admin') {
            abort(404);
        }

        // Old numeric links (/products/12) and other spellings redirect
        // permanently to the canonical slug URL, keeping any query string.
        if ($request->route()->originalParameter('product') !== $product->slug) {
            $target = route('products.show', ['locale' => $locale, 'product' => $product]);
            $query = $request->getQueryString();

            return redirect()->to($target . ($query ? '?' . $query : ''), 301);
        }

        $product->load(['category', 'approvedReviews']);
        $reviews = $product->approvedReviews;
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['category', 'images', 'variants'])
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'reviews', 'relatedProducts'));
    }

}
