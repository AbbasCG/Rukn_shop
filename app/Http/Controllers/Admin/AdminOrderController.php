<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
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
     * Works per variant for products sold in sizes/colours (and keeps
     * products.stock, their sum, in step). Rows are locked; taking stock fails
     * if there is not enough left.
     */
    private function adjustStock(Order $order, int $direction): void
    {
        // Lines whose product was deleted (product_id null) have no stock to adjust.
        $lines = $order->items->whereNotNull('product_id');

        $products = Product::whereIn('id', $lines->pluck('product_id')->unique())->lockForUpdate()->get()->keyBy('id');
        $variants = ProductVariant::whereIn('id', $lines->pluck('variant_id')->filter()->unique())->lockForUpdate()->get()->keyBy('id');

        // Check everything first, then change, so a refusal leaves nothing half-done.
        if ($direction < 0) {
            foreach ($lines->groupBy(fn ($l) => $l->product_id . ':' . ($l->variant_id ?? '')) as $group) {
                $line = $group->first();
                $quantity = (int) $group->sum('quantity');
                $variant = $line->variant_id ? $variants->get($line->variant_id) : null;
                $product = $products->get($line->product_id);
                $available = $variant ? $variant->stock : ($product?->stock ?? 0);

                if ($product && ($variant || ! $line->variant_id) && $available < $quantity) {
                    throw new InsufficientStockException(__('admin.orders.messages.not_enough_stock', [
                        'product' => $line->full_name,
                        'count' => max(0, $available),
                    ]));
                }
            }
        }

        foreach ($lines as $line) {
            $product = $products->get($line->product_id);
            if (! $product) {
                continue;
            }

            $variant = $line->variant_id ? $variants->get($line->variant_id) : null;
            $variant?->increment('stock', $direction * (int) $line->quantity);
            $product->increment('stock', $direction * (int) $line->quantity);
        }

        // Products sold in sizes/colours: products.stock must stay the sum of
        // their variants (also when a variant was deleted since the order).
        $products->each(fn (Product $product) => $product->syncStockFromVariants());
    }
}
