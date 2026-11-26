<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Support\OrderMails;
use App\Support\OrderStock;
use App\Models\Order;
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
        $carriers = implode(',', array_keys(config('shop.carriers', [])));
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_method' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'tracking_carrier' => "nullable|required_with:tracking_number|string|in:{$carriers}",
            'tracking_number' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9 \-]*$/'],
            'tracking_url' => 'nullable|url:https|max:500',
        ]);

        $order = Order::with('items')->findOrFail($order);

        // The shipping email goes out once, when the order is first marked as
        // shipped. If the tracking number is added or changed later, the
        // customer gets the new details.
        $isShipped = in_array($validated['status'], ['shipped', 'delivered'], true);
        $firstShipment = $validated['status'] === 'shipped' && $order->shipped_at === null;
        if ($request->exists('tracking_number')) {
            $validated['tracking_number'] = filled($validated['tracking_number'] ?? null)
                ? strtoupper(trim($validated['tracking_number'])) : null;
        }
        $trackingChanged = $order->shipped_at !== null && $isShipped
            && filled($validated['tracking_number'] ?? null)
            && ($validated['tracking_number'] !== $order->tracking_number
                || ($validated['tracking_carrier'] ?? null) !== $order->tracking_carrier);
        if ($firstShipment) {
            $validated['shipped_at'] = now();
        }

        $wasCancelled = $order->status === 'cancelled';
        $isCancelled = $validated['status'] === 'cancelled';

        try {
            DB::transaction(function () use ($order, $validated, $wasCancelled, $isCancelled) {
                if (! $wasCancelled && $isCancelled) {
                    OrderStock::release($order);   // cancelled: items go back on the shelf
                } elseif ($wasCancelled && ! $isCancelled) {
                    OrderStock::take($order);   // re-activated: take them again
                }

                $order->update($validated);
            });
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        $redirect = redirect()->route('admin.orders.show', $order)->with('success', __('admin.orders.messages.updated'));

        if ($firstShipment || $trackingChanged) {
            $sent = OrderMails::sendShipped($order->fresh(), trackingUpdate: ! $firstShipment);
            $redirect->with($sent ? 'success' : 'warning', $sent
                ? __('admin.orders.messages.shipped_email_sent', ['email' => $order->email])
                : __('admin.orders.messages.shipped_email_failed'));
        }

        return $redirect;
    }
}
