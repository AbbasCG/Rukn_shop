<?php

namespace App\Support;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Online payment of an order through Mollie.
 *
 * Flow: start() creates a Mollie payment and returns its checkout URL. Mollie
 * then calls the webhook (and the customer comes back to the return page);
 * both fetch the payment from the Mollie API and call apply(), which is
 * idempotent: applying the same status twice changes nothing.
 */
class OrderPayment
{
    /**
     * Create a Mollie payment for the order and remember its id.
     *
     * @return string URL of the Mollie checkout to send the customer to
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public static function start(Order $order): string
    {
        $locale = $order->locale ?: app()->getLocale();

        $payment = Mollie::createPayment([
            'amount' => ['currency' => 'EUR', 'value' => number_format((float) $order->total, 2, '.', '')],
            'description' => __('checkout.payment.description', ['number' => $order->number], $locale),
            'redirectUrl' => route('payments.return', [
                'locale' => $locale, 'order' => $order->id, 'token' => $order->access_token,
            ]),
            'webhookUrl' => route('payments.webhook'),
            'locale' => Mollie::locale($locale),
            'metadata' => ['order_id' => $order->id],
        ]);

        $order->forceFill([
            'payment_reference' => $payment['id'],
            'payment_status' => $payment['status'] ?? 'open',
        ])->save();

        return $payment['_links']['checkout']['href'];
    }

    /**
     * Bring the order in line with a Mollie payment (as returned by the API).
     */
    public static function apply(Order $order, array $payment): void
    {
        $status = (string) ($payment['status'] ?? '');
        $justPaid = false;

        DB::transaction(function () use ($order, $payment, $status, &$justPaid) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $isCurrent = ($payment['id'] ?? null) === $order->payment_reference;

            if ($status === 'paid') {
                // Money received always counts, even from an older payment attempt.
                if ($order->payment_status === 'paid') {
                    return; // already handled
                }

                if ($order->status === 'cancelled') {
                    // Paid after the order expired and was restocked: take the items again.
                    try {
                        OrderStock::take($order);
                        $order->status = 'pending';
                    } catch (InsufficientStockException $e) {
                        Log::critical('Order paid after cancellation, but stock is gone. Refund or contact the customer.', [
                            'order_id' => $order->id, 'payment_id' => $payment['id'] ?? null,
                        ]);
                    }
                }

                $order->forceFill([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'payment_reference' => $payment['id'] ?? $order->payment_reference,
                ])->save();
                $justPaid = true;

                return;
            }

            // A later attempt replaced this payment: its other outcomes no longer matter.
            if (! $isCurrent || $order->payment_status === 'paid') {
                return;
            }

            if ($status === 'expired') {
                // Never paid: free the reserved items.
                if ($order->status !== 'cancelled') {
                    OrderStock::release($order);
                    $order->status = 'cancelled';
                }
                $order->payment_status = 'expired';
                $order->save();

                return;
            }

            if (in_array($status, ['failed', 'canceled', 'open', 'pending', 'authorized'], true)) {
                // Failed / canceled: the customer can try again from the return page.
                $order->forceFill(['payment_status' => $status])->save();
            }
        });

        if ($justPaid) {
            // Emails only once the money is in.
            OrderMails::sendPlaced($order->fresh());
        }
    }

    /**
     * Fetch the payment from Mollie and apply it. Returns false when Mollie
     * could not be reached (the webhook will retry).
     */
    public static function refresh(Order $order, ?string $paymentId = null): bool
    {
        $paymentId ??= $order->payment_reference;
        if (! $paymentId) {
            return false;
        }

        try {
            self::apply($order, Mollie::getPayment($paymentId));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Could not fetch Mollie payment.', [
                'order_id' => $order->id, 'payment_id' => $paymentId, 'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
