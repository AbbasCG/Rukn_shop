<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $tokens = preg_split('/\s+/u', trim($validated['q'])) ?: [];

            foreach ($tokens as $token) {
                $query->where(function ($builder) use ($token) {
                    $likeToken = "%{$token}%";

                    $builder->where('phone', 'like', $likeToken)
                        ->orWhere('name', 'like', $likeToken)
                        ->orWhere('email', 'like', $likeToken)
                        ->orWhere('id', 'like', $likeToken)
                        ->orWhereHas('orderItems', fn ($items) => $items->where('product_name', 'like', $likeToken))
                        ->orWhereHas('user', function ($userQuery) use ($likeToken) {
                            $userQuery->where('name', 'like', $likeToken)
                                ->orWhere('email', 'like', $likeToken);
                        });
                });
            }
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

        $order = Order::with('items')->findOrFail($order);

        $wasCancelled = $order->status === 'cancelled';
        $isCancelled = $validated['status'] === 'cancelled';

        try {
            DB::transaction(function () use ($order, $validated, $wasCancelled, $isCancelled) {
                if (! $wasCancelled && $isCancelled) {
                    $this->adjustStock($order, +1);   // cancelled: items go back on the shelf
                } elseif ($wasCancelled && ! $isCancelled) {
                    $this->adjustStock($order, -1);   // re-activated: take them again
                }

                $order->update($validated);
            });
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order)->with('success', __('admin.orders.messages.updated'));
    }

    /**
     * Put an order's quantities back into stock (+1) or take them out again (-1).
     * Products are locked; taking stock fails if there is not enough left.
     */
    private function adjustStock(Order $order, int $direction): void
    {
        // Lines whose product was deleted (product_id null) have no stock to adjust.
        $quantities = $order->items
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->map(fn ($items) => (int) $items->sum('quantity'));
        $products = Product::whereIn('id', $quantities->keys())->lockForUpdate()->get()->keyBy('id');

        foreach ($quantities as $productId => $quantity) {
            $product = $products->get($productId);
            if (! $product) {
                continue;
            }

            if ($direction < 0 && $product->stock < $quantity) {
                throw new InsufficientStockException(__('admin.orders.messages.not_enough_stock', [
                    'product' => $product->name,
                    'count' => max(0, $product->stock),
                ]));
            }

            $product->increment('stock', $direction * $quantity);
        }
    }
}
