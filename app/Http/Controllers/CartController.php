<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItems = collect();
        if (Auth::check()) {
            $cartItems = Cart::where('user_id', Auth::id())
                ->with('product')
                ->get();
        } else {
            $sessionItems = collect(session('cart.items', [])); // [product_id => quantity]
            $cartItems = $sessionItems->map(function ($qty, $productId) {
                $obj = new \stdClass();
                $obj->product = \App\Models\Product::find($productId);
                $obj->quantity = (int)$qty;
                $obj->price_at_time = null;
                return $obj;
            });
        }

        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->price_at_time ?? optional($item->product)->price ?? 0;
            $qtyCol = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';
            $qty = isset($item->$qtyCol) ? $item->$qtyCol : ($item->quantity ?? 1);
            return $price * $qty;
        });
        $shipping = 0.00; // placeholder
        $total = $subtotal + $shipping;

        return view('cart.index', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = (int)($request->quantity ?? 1);

        if (Auth::check()) {
            $existingCartItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->where('status', 'active')
                ->first();

            if ($existingCartItem) {
                $quantityColumn = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';
                $existingCartItem->$quantityColumn = ($existingCartItem->$quantityColumn ?? 0) + $quantity;
                $existingCartItem->save();
            } else {
                $product = \App\Models\Product::find($request->product_id);
                Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'quantity' => $quantity,
                    'status' => 'active',
                    'price_at_time' => optional($product)->price,
                ]);
            }
            $qtyCol = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';
            $count = Cart::where('user_id', Auth::id())->sum($qtyCol);
        } else {
            // Session-based cart for guests
            $items = session('cart.items', []);
            $items[$request->product_id] = ($items[$request->product_id] ?? 0) + $quantity;
            session(['cart.items' => $items]);
            $count = array_sum($items);
            session(['cart.count' => $count]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'count' => (int)$count]);
        }
        return redirect()->back()->with('success', __('cart.flash.added'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $quantityColumn = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';
        $cart->$quantityColumn = $request->quantity;
        $cart->save();

        if ($request->expectsJson()) {
            // return updated item total and cart totals
            $price = $cart->price_at_time ?? optional($cart->product)->price ?? 0;
            $itemTotal = $price * $cart->$quantityColumn;
            $items = Cart::where('user_id', Auth::id())->with('product')->get();
            $subtotal = $items->sum(function ($item) {
                $price = $item->price_at_time ?? optional($item->product)->price ?? 0;
                $qtyCol = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';
                return $price * ($item->$qtyCol ?? 1);
            });
            $shipping = 0.00;
            $total = $subtotal + $shipping;
            return response()->json([
                'ok' => true,
                'itemTotal' => $itemTotal,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
            ]);
        }
        return back()->with('success', __('cart.flash.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }
        $cart->delete();
        return back()->with('success', __('cart.flash.removed'));
    }

    /**
     * Clear all active cart items for user
     */
    public function clear(Request $request)
    {
        Cart::where('user_id', Auth::id())->where('status', 'active')->delete();
        return back()->with('success', __('cart.flash.cleared'));
    }
}
