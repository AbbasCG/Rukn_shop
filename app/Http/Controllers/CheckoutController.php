<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\order_items as OrderItem;
use App\Models\Cart;
use App\Models\Product;
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
        $userId = Auth::id();
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('checkout.flash.empty_cart'));
        }
        // Always the current product price: a price changed after the item was
        // added to the cart applies at checkout.
        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->product->price;
            $qtyCol = isset($item->quantity) ? 'quantity' : (property_exists($item, 'quanttty') ? 'quanttty' : 'quantity');
            return $price * ($item->$qtyCol ?? 1);
        });
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

        $userId = Auth::id();
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('checkout.flash.empty_cart'));
        }

        DB::beginTransaction();
        try {
            // Lock the product rows so two simultaneous orders cannot both
            // take the last items in stock.
            $products = Product::whereIn('id', $cartItems->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);
                $qty = (int) ($item->quantity ?? 1);

                if (! $product || ! $product->is_active) {
                    DB::rollBack();
                    return redirect()->route('cart.index')->with('error', __('checkout.flash.unavailable', [
                        'product' => optional($item->product)->name ?? '#' . $item->product_id,
                    ]));
                }

                if ($product->stock < $qty) {
                    DB::rollBack();
                    return redirect()->route('cart.index')->with('error', __('checkout.flash.stock_changed', [
                        'product' => $product->name,
                        'count' => max(0, $product->stock),
                    ]));
                }
            }

            // Prices from the locked product rows: what the customer saw at
            // checkout, and cannot change while the order is being written.
            $subtotal = $cartItems->sum(function ($item) use ($products) {
                $price = $products->get($item->product_id)->price;
                $qtyCol = isset($item->quantity) ? 'quantity' : (property_exists($item, 'quanttty') ? 'quanttty' : 'quantity');
                return $price * ($item->$qtyCol ?? 1);
            });
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

            foreach ($cartItems as $item) {
                $price = $products->get($item->product_id)->price;
                $qtyCol = isset($item->quantity) ? 'quantity' : (property_exists($item, 'quanttty') ? 'quanttty' : 'quantity');
                $qty = $item->$qtyCol ?? 1;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $products->get($item->product_id)->name,
                    'quantity' => $qty,
                    'price' => $price,
                ]);

                $products->get($item->product_id)->decrement('stock', $qty);
            }

            // Clear cart
            Cart::where('user_id', $userId)->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', __('checkout.flash.failed'));
        }

        // After the commit, and outside the transaction: a mail problem must
        // never undo or hide an order that has been placed.
        $this->sendOrderEmails($order);

        return redirect()->route('orders.confirmation', $order);
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
        if ($order->user_id !== Auth::id()) {
            abort(404);
        }
        return view('orders.confirmation', compact('order'));
    }
}
