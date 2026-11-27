<?php

namespace App\Console\Commands;

use App\Mail\ReviewRequest;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails buyers a review request a few days after their order was marked
 * delivered (shop.reviews.request_after_days). Runs hourly from the
 * scheduler; each order is asked once.
 */
class SendReviewRequests extends Command
{
    protected $signature = 'reviews:send-requests';

    protected $description = 'Email customers whose order was delivered a few days ago and ask them for a review';

    public function handle(): int
    {
        $days = (int) config('shop.reviews.request_after_days', 7);
        $sent = 0;

        Order::query()
            ->where('status', 'delivered')
            ->whereNull('review_requested_at')
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', now()->subDays($days))
            // Orders delivered long before this feature existed are not emailed.
            ->where('delivered_at', '>=', now()->subDays($days + 30))
            ->orderBy('id')
            ->each(function (Order $order) use (&$sent) {
                // Mark first: a mail problem must never lead to a second email.
                $order->forceFill(['review_requested_at' => now()])->save();

                if ($order->reviewableProducts()->isEmpty() || blank($order->email)) {
                    return;
                }

                try {
                    Mail::to($order->email, $order->name)->send(new ReviewRequest($order));
                    $sent++;
                } catch (\Throwable $e) {
                    Log::error('Review request email could not be queued.', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                }
            });

        $this->info("Review requests sent: {$sent}.");

        return self::SUCCESS;
    }
}
