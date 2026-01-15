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
        $query = Product::where('is_active', true);

        // Search
        if (request('q')) {
            $query->where('name', 'like', '%' . request('q') . '%')
                  ->orWhere('description', 'like', '%' . request('q') . '%');
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
                $query->inRandomOrder();
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
    public function show(Product $product)
    {
        $product->load(['category', 'reviews.user']);
        $reviews = $product->reviews;
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'reviews', 'relatedProducts'));
    }

}
