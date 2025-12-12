<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     * If user is admin, shows all orders. If user is customer, shows their orders.
     */
    public function index()
    {
        // Customer view - list their own orders
        if (Auth::check() && !Auth::user()->isAdmin()) {
            $orders = Auth::user()->orders()->latest()->paginate(10);
            return view('orders.index', compact('orders'));
        }

        // Admin view - list all orders
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $orders = Order::with(['user', 'orderItems.product'])
            ->latest()
            ->paginate(20);
        
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource (admin only).
     */
    public function create()
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return view('admin.orders.create');
    }

    /**
     * Store a newly created resource in storage (admin only).
     */
    public function store(Request $request)
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_method' => 'nullable|string',
            'shipping_address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $order = Order::create($validated);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified order.
     * Customers can only view their own orders, admins can view all orders.
     */
    public function show(Order $order)
    {
        // Customer view - can only view their own orders
        if (Auth::check() && !Auth::user()->isAdmin()) {
            if ($order->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access to this order.');
            }
            $order->load('items.product');
            return view('orders.show', compact('order'));
        }

        // Admin view
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $order->load(['user', 'orderItems.product']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource (admin only).
     */
    public function edit(Order $order)
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified resource in storage (admin only).
     */
    public function update(Request $request, Order $order)
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_method' => 'nullable|string',
            'shipping_address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage (admin only).
     */
    public function destroy(Order $order)
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        // Delete associated order items first
        $order->orderItems()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
