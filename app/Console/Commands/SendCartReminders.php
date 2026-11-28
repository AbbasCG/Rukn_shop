<?php

namespace App\Console\Commands;

use App\Mail\CartReminder;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * "You left something in your cart": at most two emails per cart to
 * customers with an account (24 hours and 3 days after the last change,
 * see shop.cart_reminders). Any change to the cart starts over; customers
 * can turn the emails off. Runs hourly from the scheduler.
 */
class SendCartReminders extends Command
{
    protected $signature = 'cart:send-reminders';

    protected $description = 'Remind customers with an account about products left in their cart';

    public function handle(): int
    {
        if (! config('shop.cart_reminders.enabled')) {
            $this->info('Cart reminders are switched off.');

            return self::SUCCESS;
        }

        $first = (int) config('shop.cart_reminders.first_after_hours', 24);
        $second = (int) config('shop.cart_reminders.second_after_hours', 72);
        $oldest = now()->subDays((int) config('shop.cart_reminders.max_age_days', 14));
        $sent = 0;

        $lastChanges = Cart::query()
            ->where('status', 'active')
            ->selectRaw('user_id, max(updated_at) as last_change')
            ->groupBy('user_id')
            ->pluck('last_change', 'user_id');

        User::query()
            ->whereIn('id', $lastChanges->keys())
            ->where('cart_reminders', true)
            ->where('role', '!=', 'admin')
            ->orderBy('id')
            ->each(function (User $user) use ($lastChanges, $first, $second, $oldest, &$sent) {
                $lastChange = Carbon::parse($lastChanges[$user->id]);
                if ($lastChange->lt($oldest)) {
                    return;   // an old, forgotten cart: leave it alone
                }

                // Reminders count per cart: a changed cart starts again at zero.
                $already = $user->cart_reminder_for?->getTimestamp() === $lastChange->getTimestamp()
                    ? (int) $user->cart_reminders_sent
                    : 0;

                $due = match ($already) {
                    0 => $lastChange->lte(now()->subHours($first)),
                    1 => $lastChange->lte(now()->subHours($second)),
                    default => false,
                };
                if (! $due || ! self::hasSomethingToBuy($user)) {
                    return;
                }

                // Mark first: a mail problem must never lead to a duplicate.
                $user->forceFill(['cart_reminder_for' => $lastChange, 'cart_reminders_sent' => $already + 1])->saveQuietly();

                try {
                    Mail::to($user->email, $user->name)->queue(new CartReminder($user, $already + 1));
                    $sent++;
                } catch (\Throwable $e) {
                    Log::error('Cart reminder could not be queued.', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                }
            });

        $this->info("Cart reminders sent: {$sent}.");

        return self::SUCCESS;
    }

    /** At least one line can still be bought (visible and in stock). */
    public static function hasSomethingToBuy(User $user): bool
    {
        return self::lines($user)->isNotEmpty();
    }

    /** The customer's cart lines that can still be bought. */
    public static function lines(User $user): \Illuminate\Support\Collection
    {
        return Cart::where('user_id', $user->id)->where('status', 'active')
            ->with(['product.images', 'product.variants', 'variant.product'])
            ->get()
            ->filter(fn (Cart $line) => $line->product && $line->isValid() && $line->availableStock() > 0)
            ->values();
    }
}
