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

        // Price filter - handle both price_range dropdown and direct min/max_price inputs
        if (request('price_range')) {
            $priceRange = request('price_range');
            if ($priceRange === '0-25') {
                $query->where('price', '<=', 25);
            } elseif ($priceRange === '25-50') {
                $query->whereBetween('price', [25, 50]);
            } elseif ($priceRange === '50-100') {
                $query->whereBetween('price', [50, 100]);
            } elseif ($priceRange === '100+') {
                $query->where('price', '>=', 100);
            }
        } else {
            if (request('min_price')) {
                $query->where('price', '>=', request('min_price'));
            }
            if (request('max_price')) {
                $query->where('price', '<=', request('max_price'));
            }
        }

        // Sorting
        $sort = request('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
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

        return view('products.index', compact('products', 'categories'));
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
