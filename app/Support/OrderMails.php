<?php

namespace App\Support;

use App\Mail\NewOrderNotification;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails sent when an order is placed (pay on delivery) or paid (online).
 */
class OrderMails
{
    /**
     * Customer confirmation + staff notification. Each is sent separately so
     * one failing does not stop the other; failures are logged only and never
     * affect the order.
     */
    public static function sendPlaced(Order $order): void
    {
        self::sendSafely('Order confirmation email could not be sent.', $order,
            fn () => Mail::to($order->email, $order->name)->send(new OrderConfirmation($order)));

        $staff = config('shop.order_notifications.emails', []);
        if (! empty($staff)) {
            self::sendSafely('New order notification could not be sent.', $order,
                fn () => Mail::to($staff)->send(new NewOrderNotification($order)));
        }
    }

    private static function sendSafely(string $message, Order $order, callable $send): void
    {
        try {
            $send();
        } catch (\Throwable $e) {
            Log::error($message, ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
