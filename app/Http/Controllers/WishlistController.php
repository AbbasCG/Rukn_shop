<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Wishlist;
use Illuminate\Http\Request;

/**
 * Favourites: the heart buttons and the favourites page.
 */
class WishlistController extends Controller
{
    public function index()
    {
        $ids = Wishlist::ids();
        $products = Product::whereIn('id', $ids)->where('is_active', true)
            ->with(['category', 'images', 'variants'])
            ->get()
            ->sortBy(fn (Product $p) => array_search($p->id, $ids, true))
            ->values();

        return view('wishlist.index', compact('products'));
    }

    public function toggle(Request $request, string $locale, Product $product)
    {
        abort_unless($product->is_active || Wishlist::has($product->id), 404);

        $saved = Wishlist::toggle($product);
        $message = $saved ? __('wishlist.added') : __('wishlist.removed');

        if ($request->expectsJson()) {
            return response()->json(['saved' => $saved, 'count' => Wishlist::count(), 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
