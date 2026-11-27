<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Reviews are written from an order (the private link in the review request
 * email or on the order page), so only real buyers can review, with or
 * without an account. New reviews wait for the shop's approval.
 */
class ReviewController extends Controller
{
    public function create(string $locale, int $order, string $token): View
    {
        $order = $this->authorizedOrder($order, $token);

        return view('reviews.create', [
            'order' => $order,
            'products' => $order->canBeReviewed() ? $order->reviewableProducts() : collect(),
            'submitted' => $order->reviews()->with('product')->latest('id')->get(),
            'defaultName' => $this->firstName($order->name),
        ]);
    }

    public function store(Request $request, string $locale, int $order, string $token): RedirectResponse
    {
        $order = $this->authorizedOrder($order, $token);
        abort_unless($order->canBeReviewed(), 403);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::in($order->reviewableProducts()->pluck('id')->all())],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'author_name' => ['required', 'string', 'max:60'],
        ], [
            'product_id.in' => __('reviews.form.already_reviewed'),
        ]);

        Review::create($validated + [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'status' => 'pending',
            'locale' => app()->getLocale(),
        ]);

        return redirect()->to($order->reviewUrl($locale))->with('status', __('reviews.form.thanks'));
    }

    /** Shown with the review: first name only, unless the buyer changes it. */
    private function firstName(?string $name): string
    {
        return (string) (preg_split('/\s+/u', trim((string) $name))[0] ?? '');
    }

    private function authorizedOrder(int $id, string $token): Order
    {
        $order = Order::with('items.product')->find($id);

        if (! $order || ! $order->access_token || ! hash_equals($order->access_token, $token)) {
            abort(404);
        }

        return $order;
    }
}
