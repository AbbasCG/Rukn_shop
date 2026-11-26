<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
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
                $obj->product = Product::find($productId);
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
        $product = Product::find($request->product_id);

        // Inactive or sold-out products cannot be added at all.
        if (! $product->is_active || $product->stock <= 0) {
            return $this->cartResponse($request, false, __('cart.flash.out_of_stock'));
        }

        $quantityColumn = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';
        $existingCartItem = null;

        if (Auth::check()) {
            $existingCartItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->first();
            $alreadyInCart = (int) ($existingCartItem->$quantityColumn ?? 0);
        } else {
            $items = session('cart.items', []);
            $alreadyInCart = (int) ($items[$product->id] ?? 0);
        }

        // Never let the cart hold more than what is in stock.
        $newQuantity = min($alreadyInCart + $quantity, $product->stock);
        $limited = $newQuantity < $alreadyInCart + $quantity;

        if ($newQuantity <= $alreadyInCart) {
            return $this->cartResponse($request, false, __('cart.flash.stock_limited', ['count' => $product->stock]));
        }

        if (Auth::check()) {
            if ($existingCartItem) {
                $existingCartItem->$quantityColumn = $newQuantity;
                $existingCartItem->save();
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'quantity' => $newQuantity,
                    'status' => 'active',
                    'price_at_time' => $product->price,
                ]);
            }
            $count = Cart::where('user_id', Auth::id())->sum($quantityColumn);
        } else {
            // Session-based cart for guests
            $items[$product->id] = $newQuantity;
            session(['cart.items' => $items]);
            $count = array_sum($items);
            session(['cart.count' => $count]);
        }

        $message = $limited
            ? __('cart.flash.stock_limited', ['count' => $product->stock])
            : __('cart.flash.added');

        return $this->cartResponse($request, true, $message, (int) $count, $limited);
    }

    /**
     * Shared JSON / redirect response for add-to-cart.
     */
    private function cartResponse(Request $request, bool $ok, string $message, ?int $count = null, bool $warning = false)
    {
        if ($request->expectsJson()) {
            $payload = ['ok' => $ok, 'message' => $message];
            if ($count !== null) {
                $payload['count'] = $count;
            }
            return response()->json($payload, $ok ? 200 : 422);
        }

        $type = ! $ok ? 'error' : ($warning ? 'warning' : 'success');
        return redirect()->back()->with($type, $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $locale, Cart $cart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $locale, Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $locale, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $quantityColumn = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';

        // Cap the requested quantity at what is actually in stock.
        $stock = max(0, (int) optional($cart->product)->stock);
        $requested = (int) $request->quantity;
        $limited = $requested > $stock;
        $cart->$quantityColumn = max(1, min($requested, $stock));
        $cart->save();

        if ($limited && ! $request->expectsJson()) {
            return back()->with('warning', __('cart.flash.stock_limited', ['count' => $stock]));
        }

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
                'quantity' => (int) $cart->$quantityColumn,
                'message' => $limited ? __('cart.flash.stock_limited', ['count' => $stock]) : null,
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
    public function destroy(string $locale, Cart $cart)
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
