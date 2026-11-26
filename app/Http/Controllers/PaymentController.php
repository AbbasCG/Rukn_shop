<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Mollie;
use App\Support\OrderPayment;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Mollie webhook: Mollie posts only a payment id. The payment is fetched
     * from the Mollie API (the post itself is not trusted) and the order is
     * found through the payment's metadata.
     */
    public function webhook(Request $request)
    {
        $id = (string) $request->input('id', '');
        if (! preg_match('/^tr_[A-Za-z0-9]+$/', $id)) {
            return response('', 200); // nothing to do; don't invite retries
        }

        try {
            $payment = Mollie::getPayment($id);
        } catch (RequestException $e) {
            if ($e->response->status() === 404) {
                return response('', 200); // not a payment of this shop (e.g. another API key)
            }
            Log::warning('Mollie webhook: payment could not be fetched.', ['payment_id' => $id, 'error' => $e->getMessage()]);

            return response('', 503); // Mollie retries later
        } catch (\Throwable $e) {
            Log::warning('Mollie webhook: payment could not be fetched.', ['payment_id' => $id, 'error' => $e->getMessage()]);

            return response('', 503); // Mollie retries later
        }

        $order = Order::find((int) ($payment['metadata']['order_id'] ?? 0));
        if ($order) {
            OrderPayment::apply($order, $payment);
        }

        return response('', 200);
    }

    /**
     * Where Mollie sends the customer back. The webhook may not have arrived
     * yet, so the payment is checked here too.
     */
    public function return(string $locale, int $order, string $token)
    {
        $order = $this->authorizedOrder($order, $token);

        if ($order->payment_status !== 'paid') {
            OrderPayment::refresh($order);
            $order->refresh();
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.placed', ['order' => $order->id, 'token' => $order->access_token]);
        }

        $pending = in_array($order->payment_status, ['open', 'pending', 'authorized'], true);
        $canRetry = ! $pending && $order->status !== 'cancelled';

        return response()->view('payments.status', compact('order', 'pending', 'canRetry'));
    }

    /**
     * Start a new payment attempt for an unpaid order.
     */
    public function retry(string $locale, int $order, string $token)
    {
        $order = $this->authorizedOrder($order, $token);

        if ($order->payment_status === 'paid' || $order->status === 'cancelled' || $order->payment_method !== 'mollie') {
            return redirect()->route('payments.return', ['order' => $order->id, 'token' => $order->access_token]);
        }

        try {
            return redirect()->away(OrderPayment::start($order));
        } catch (\Throwable $e) {
            Log::error('Mollie payment retry could not be created.', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return back()->with('error', __('checkout.payment.start_failed'));
        }
    }

    private function authorizedOrder(int $id, string $token): Order
    {
        $order = Order::find($id);

        if (! $order || ! $order->access_token || ! hash_equals($order->access_token, $token)) {
            abort(404);
        }

        return $order;
    }
}
