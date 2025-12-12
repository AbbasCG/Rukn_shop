<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\order_items as OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        $subtotal = $cartItems->sum(function($item){
            $price = $item->price_at_time ?? $item->product->price;
            $qtyCol = isset($item->quantity) ? 'quantity' : (property_exists($item, 'quanttty') ? 'quanttty' : 'quantity');
            return $price * ($item->$qtyCol ?? 1);
        });
        $shipping = 0.00; // flat rate placeholder
        $total = $subtotal + $shipping;
        return view('checkout.index', compact('cartItems','subtotal','shipping','total'));
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
            'shipping_method' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        $userId = Auth::id();
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        DB::beginTransaction();
        try {
            $subtotal = $cartItems->sum(function($item){
                $price = $item->price_at_time ?? $item->product->price;
                $qtyCol = isset($item->quantity) ? 'quantity' : (property_exists($item, 'quanttty') ? 'quanttty' : 'quantity');
                return $price * ($item->$qtyCol ?? 1);
            });
            $shipping = 0.00;
            $total = $subtotal + $shipping;

            $order = Order::create([
                'user_id' => $userId,
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
                $price = $item->price_at_time ?? $item->product->price;
                $qtyCol = isset($item->quantity) ? 'quantity' : (property_exists($item, 'quanttty') ? 'quanttty' : 'quantity');
                $qty = $item->$qtyCol ?? 1;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $qty,
                    'price' => $price,
                ]);
            }

            // Clear cart
            Cart::where('user_id', $userId)->delete();

            DB::commit();
            return redirect()->route('orders.confirmation', $order);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to place order. Please try again.');
        }
    }

    public function confirmation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(404);
        }
        return view('orders.confirmation', compact('order'));
    }
}
