<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\GuestCart;
use App\Support\OrderMails;
use App\Support\OrderPayment;
use App\Support\OrderStock;
use App\Support\Payments;
use App\Support\CartTotals;
use App\Support\Shipping;
use App\Support\Vat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = $this->cartLines();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('checkout.flash.empty_cart'));
        }
        // Always the current price (the variant's, else the product's): a price
        // changed after the item was added to the cart applies at checkout.
        $subtotal = $cartItems->sum(fn (Cart $item) => $item->unitPrice() * $item->quantity);
        $totals = CartTotals::for((float) $subtotal);
        $shipping = $totals->shipping;
        $total = $totals->total;
        return view('checkout.index', compact('cartItems', 'subtotal', 'shipping', 'total', 'totals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:30',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'shipping_method' => 'required|in:flat',
            'payment_method' => ['required', Rule::in(Payments::methods())],
        ]);

        $userId = Auth::id(); // null for guests
        $cartItems = $this->cartLines();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('checkout.flash.empty_cart'));
        }

        DB::beginTransaction();
        try {
            // Lock the product and variant rows so two simultaneous orders cannot
            // both take the last items in stock.
            $products = Product::with('variants')
                ->whereIn('id', $cartItems->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $variants = ProductVariant::whereIn('id', $cartItems->pluck('variant_id')->filter())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate every line against the locked rows and work out its price.
            $lines = [];
            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);
                $variant = $item->variant_id ? $variants->get($item->variant_id) : null;
                $qty = (int) ($item->quantity ?? 1);
                $label = $variant ? $variant->setRelation('product', $product)->label() : null;
                $displayName = ($product->name ?? '#' . $item->product_id) . ($label ? " ({$label})" : '');

                $variantOk = $product && ($product->variants->isNotEmpty()
                    ? $variant !== null && $variant->product_id === $product->id
                    : $item->variant_id === null);

                if (! $product || ! $product->is_active || ! $variantOk) {
                    DB::rollBack();
                    return redirect()->route('cart.index')->with('error', __('checkout.flash.unavailable', [
                        'product' => $displayName,
                    ]));
                }

                $stock = $variant ? $variant->stock : $product->stock;
                if ($stock < $qty) {
                    DB::rollBack();
                    return redirect()->route('cart.index')->with('error', __('checkout.flash.stock_changed', [
                        'product' => $displayName,
                        'count' => max(0, $stock),
                    ]));
                }

                // Prices from the locked rows: what the customer saw at checkout,
                // and cannot change while the order is being written.
                $lines[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'label' => $label,
                    'qty' => $qty,
                    'price' => $variant ? $variant->effectivePrice() : (float) $product->price,
                ];
            }

            $subtotal = collect($lines)->sum(fn ($line) => $line['price'] * $line['qty']);
            // Re-checked now: a code may have been switched off since the cart page.
            $totals = CartTotals::for((float) $subtotal);
            $shipping = $totals->shipping;
            $total = $totals->total;

            $order = Order::create([
                'user_id' => $userId,
                'locale' => app()->getLocale(),
                'status' => 'pending',
                'payment_status' => 'open',
                'payment_method' => $validated['payment_method'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'country' => $validated['country'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'total' => $total,
                // Prices include VAT; keep the figures of this moment on the order.
                'vat_rate' => Vat::rate(),
                'vat_amount' => Vat::included((float) $total),
                'coupon_code' => $totals->couponApplies() ? $totals->coupon->code : null,
                'discount_amount' => $totals->discount,
            ]);

            if ($totals->couponApplies()) {
                $totals->coupon->increment('uses_count');
            }

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'variant_id' => $line['variant']?->id,
                    'product_name' => $line['product']->name,
                    'variant_label' => $line['label'],
                    'quantity' => $line['qty'],
                    'price' => $line['price'],
                ]);

                // products.stock is the sum of the variants' stock, so both go down.
                $line['variant']?->decrement('stock', $line['qty']);
                $line['product']->decrement('stock', $line['qty']);
            }

            // Clear cart
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', __('checkout.flash.failed'));
        }

        if ($order->payment_method === 'mollie') {
            return $this->startOnlinePayment($order, $userId);
        }

        $this->clearCart($userId);

        // After the commit, and outside the transaction: a mail problem must
        // never undo or hide an order that has been placed.
        OrderMails::sendPlaced($order);

        // Guests have no account: their confirmation page is behind the order's
        // private link (also in the confirmation email).
        return $userId
            ? redirect()->route('orders.confirmation', $order)
            : redirect()->route('orders.placed', ['order' => $order->id, 'token' => $order->access_token]);
    }

    public function confirmation(string $locale, string $order)
    {
        $order = Order::findOrFail($order);
        // Guest orders are only reachable through their private link.
        if ($order->user_id === null || $order->user_id !== Auth::id()) {
            abort(404);
        }
        return view('orders.confirmation', compact('order'));
    }

    /**
     * Cart lines of a logged-in customer, or of a guest (from the session).
     */
    private function cartLines(): \Illuminate\Support\Collection
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->with(['product.variants', 'variant.product'])->get();
        }

        return GuestCart::lines();
    }

    /**
     * Online payment: send the customer to Mollie. The cart is only emptied
     * once the payment exists; if Mollie cannot be reached the order is
     * cancelled, its stock released, and the customer keeps their cart.
     * Emails are sent when the payment is confirmed (OrderPayment::apply).
     */
    private function startOnlinePayment(Order $order, ?int $userId)
    {
        try {
            $checkoutUrl = OrderPayment::start($order);
        } catch (\Throwable $e) {
            Log::error('Mollie payment could not be created.', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            DB::transaction(function () use ($order) {
                OrderStock::release($order);
                $order->forceFill(['status' => 'cancelled', 'payment_status' => 'failed'])->save();
                // The order never happened: its discount code use does not count.
                if ($order->coupon_code) {
                    \App\Models\Coupon::where('code', $order->coupon_code)->where('uses_count', '>', 0)->decrement('uses_count');
                }
            });

            return redirect()->route('checkout.index')->withInput()
                ->with('error', __('checkout.payment.start_failed'));
        }

        $this->clearCart($userId);

        return redirect()->away($checkoutUrl);
    }

    private function clearCart(?int $userId): void
    {
        session()->forget(CartTotals::SESSION_KEY);

        if ($userId) {
            Cart::where('user_id', $userId)->delete();
        } else {
            GuestCart::put([]);
        }
    }
}
