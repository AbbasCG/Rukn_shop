<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Orders seen without logging in: the private link from the confirmation
 * email / confirmation page, and the "find my order" form.
 */
class GuestOrderController extends Controller
{
    /**
     * Private order page. A wrong token is a 404, so it does not even
     * reveal whether the order exists.
     */
    public function show(string $locale, int $order, string $token)
    {
        $order = $this->authorizedOrder($order, $token);

        return view('orders.show', compact('order'));
    }

    /**
     * Thank-you page right after a guest checkout.
     */
    public function confirmation(string $locale, int $order, string $token)
    {
        $order = $this->authorizedOrder($order, $token);

        return view('orders.confirmation', compact('order'));
    }

    public function lookupForm()
    {
        return view('orders.lookup');
    }

    /**
     * Order number + the email used at checkout -> the private link.
     */
    public function lookup(Request $request, string $locale)
    {
        $validated = $request->validate([
            'number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $id = (int) ltrim(preg_replace('/\D/', '', $validated['number']), '0');
        $order = $id > 0 ? Order::find($id) : null;

        if (! $order || strcasecmp((string) $order->email, trim($validated['email'])) !== 0) {
            // Same message whether the number or the email is wrong.
            return back()->withInput()->withErrors(['number' => __('orders.lookup.not_found')]);
        }

        return redirect()->to($order->privateUrl($locale));
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
