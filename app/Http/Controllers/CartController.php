<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\GuestCart;
use App\Support\CartTotals;
use App\Support\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = $this->lines();

        $subtotal = $cartItems->sum(fn (Cart $item) => $item->unitPrice() * $item->quantity);
        $totals = CartTotals::for((float) $subtotal);
        $shipping = $totals->shipping;
        $total = $totals->total;

        return view('cart.index', compact('cartItems', 'subtotal', 'shipping', 'total', 'totals'));
    }

    /**
     * Add a product (and, for products sold in sizes/colours, a variant).
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = (int) ($request->quantity ?? 1);
        $product = Product::with('variants')->find($request->product_id);

        if (! $product->is_active) {
            return $this->cartResponse($request, false, __('cart.flash.out_of_stock'));
        }

        // Products with sizes/colours need a valid variant of this product.
        $variant = null;
        if ($product->hasVariants()) {
            $variant = $product->variants->firstWhere('id', (int) $request->variant_id);
            if (! $variant) {
                return $this->cartResponse($request, false, __('cart.flash.choose_variant'));
            }
            $variant->setRelation('product', $product);
        }

        $stock = $variant ? $variant->stock : (int) $product->stock;
        if ($stock <= 0) {
            return $this->cartResponse($request, false, __('cart.flash.out_of_stock'));
        }

        if (Auth::check()) {
            $existing = Cart::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->where('variant_id', $variant?->id)
                ->where('status', 'active')
                ->first();
            $alreadyInCart = (int) ($existing->quantity ?? 0);
        } else {
            $items = GuestCart::raw();
            $key = GuestCart::key($product->id, $variant?->id);
            $alreadyInCart = (int) ($items[$key] ?? 0);
        }

        // Never let the cart hold more than what is in stock.
        $newQuantity = min($alreadyInCart + $quantity, $stock);
        $limited = $newQuantity < $alreadyInCart + $quantity;

        if ($newQuantity <= $alreadyInCart) {
            return $this->cartResponse($request, false, __('cart.flash.stock_limited', ['count' => $stock]));
        }

        if (Auth::check()) {
            // Language for later emails (cart reminders).
            if (Auth::user()->locale !== app()->getLocale()) {
                Auth::user()->forceFill(['locale' => app()->getLocale()])->saveQuietly();
            }
            if ($existing) {
                $existing->update(['quantity' => $newQuantity]);
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity' => $newQuantity,
                    'status' => 'active',
                    'price_at_time' => $variant ? $variant->effectivePrice() : $product->currentPrice(),
                ]);
            }
            $count = (int) Cart::where('user_id', Auth::id())->sum('quantity');
        } else {
            $items[$key] = $newQuantity;
            GuestCart::put($items);
            $count = array_sum($items);
        }

        $message = $limited
            ? __('cart.flash.stock_limited', ['count' => $stock])
            : __('cart.flash.added');

        return $this->cartResponse($request, true, $message, $count, $limited);
    }

    /**
     * Change a line's quantity. {line} is a saved cart id for logged-in
     * customers, or the session key ("<productId>[:<variantId>]") for guests.
     */
    public function update(Request $request, string $locale, string $line)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->findLine($line);

        // Cap the requested quantity at what is actually in stock.
        $stock = max(0, $cart->availableStock());
        $requested = (int) $request->quantity;
        $limited = $requested > $stock;
        $cart->quantity = max(1, min($requested, $stock));

        if (Auth::check()) {
            $cart->save();
        } else {
            $items = GuestCart::raw();
            $items[$line] = (int) $cart->quantity;
            GuestCart::put($items);
        }

        if ($request->expectsJson()) {
            $items = $this->lines();
            $subtotal = $items->sum(fn (Cart $item) => $item->unitPrice() * $item->quantity);
            $shipping = CartTotals::for((float) $subtotal)->shipping;

            return response()->json([
                'ok' => true,
                'quantity' => (int) $cart->quantity,
                'message' => $limited ? __('cart.flash.stock_limited', ['count' => $stock]) : null,
                'itemTotal' => $cart->unitPrice() * $cart->quantity,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $subtotal + $shipping,
                'vat' => \App\Support\Vat::included((float) ($subtotal + $shipping)),
            ]);
        }

        if ($limited) {
            return back()->with('warning', __('cart.flash.stock_limited', ['count' => $stock]));
        }

        return back()->with('success', __('cart.flash.updated'));
    }

    public function destroy(string $locale, string $line)
    {
        $cart = $this->findLine($line);

        if (Auth::check()) {
            $cart->delete();
        } else {
            $items = GuestCart::raw();
            unset($items[$line]);
            GuestCart::put($items);
        }

        return back()->with('success', __('cart.flash.removed'));
    }

    public function clear(Request $request)
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->where('status', 'active')->delete();
        } else {
            GuestCart::put([]);
        }

        return back()->with('success', __('cart.flash.cleared'));
    }

    /**
     * The visitor's own line (404 when it is not in their cart).
     */
    private function findLine(string $line): Cart
    {
        if (Auth::check()) {
            $cart = ctype_digit($line) ? Cart::with('product', 'variant')->find((int) $line) : null;
            if ($cart && $cart->user_id !== Auth::id()) {
                abort(403);
            }
        } else {
            $cart = GuestCart::lines()->firstWhere('guest_key', $line);
        }

        abort_if($cart === null, 404);

        return $cart;
    }

    /**
     * The visitor's cart lines (saved for users, from the session for guests).
     */
    private function lines(): Collection
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())
                ->with(['product.variants', 'variant.product'])
                ->get()
                ->filter(fn (Cart $item) => $item->product !== null)
                ->values();
        }

        return GuestCart::lines();
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
}
