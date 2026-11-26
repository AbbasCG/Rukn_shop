<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\order_items as OrderItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\GuestCart;
use App\Support\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewOrderNotification;
use App\Mail\OrderConfirmation;

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
        $shipping = Shipping::cost((float) $subtotal);
        $total = $subtotal + $shipping;
        return view('checkout.index', compact('cartItems', 'subtotal', 'shipping', 'total'));
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
            'payment_method' => 'required|in:cod',
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
            $shipping = Shipping::cost((float) $subtotal);
            $total = $subtotal + $shipping;

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
            ]);

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
            if ($userId) {
                Cart::where('user_id', $userId)->delete();
            } else {
                GuestCart::put([]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', __('checkout.flash.failed'));
        }

        // After the commit, and outside the transaction: a mail problem must
        // never undo or hide an order that has been placed.
        $this->sendOrderEmails($order);

        // Guests have no account: their confirmation page is behind the order's
        // private link (also in the confirmation email).
        return $userId
            ? redirect()->route('orders.confirmation', $order)
            : redirect()->route('orders.placed', ['order' => $order->id, 'token' => $order->access_token]);
    }

    /**
     * Customer confirmation + staff notification. Each is sent separately so
     * one failing does not stop the other; failures are logged only.
     */
    private function sendOrderEmails(Order $order): void
    {
        $this->sendSafely('Order confirmation email could not be sent.', $order,
            fn () => Mail::to($order->email, $order->name)->send(new OrderConfirmation($order)));

        $staff = config('shop.order_notifications.emails', []);
        if (! empty($staff)) {
            $this->sendSafely('New order notification could not be sent.', $order,
                fn () => Mail::to($staff)->send(new NewOrderNotification($order)));
        }
    }

    private function sendSafely(string $message, Order $order, callable $send): void
    {
        try {
            $send();
        } catch (\Throwable $e) {
            Log::error($message, ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
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
}
