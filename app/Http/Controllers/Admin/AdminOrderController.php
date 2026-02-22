<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $query = Order::with(['user', 'orderItems.product'])->latest();

        if (!empty($validated['q'])) {
            $searchTerm = trim($validated['q']);

            $query->where(function ($builder) use ($searchTerm) {
                if (ctype_digit($searchTerm)) {
                    $builder->orWhere('id', (int) $searchTerm)
                        ->orWhere('phone', 'like', "%{$searchTerm}%");
                }

                $builder->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            });
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(string $locale, string $order)
    {
        $order = Order::with(['user', 'orderItems.product'])->findOrFail($order);
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, string $locale, string $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_method' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($order);
        $order->update($validated);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated successfully');
    }
}
