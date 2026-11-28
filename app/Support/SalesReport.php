<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Sales figures for a period, in the shop's time zone.
 *
 * A sale is every order that is not cancelled, except online payments that
 * were never paid (an abandoned Mollie checkout is not a sale). Pay-on-
 * delivery orders count from the moment they are placed.
 */
class SalesReport
{
    public const TIMEZONE = 'Europe/Amsterdam';

    /** Up to this many days the chart shows days, above that months. */
    public const DAILY_UP_TO_DAYS = 92;

    public function __construct(public readonly Carbon $from, public readonly Carbon $to)
    {
    }

    /**
     * Named periods for the filter. "to" is inclusive (whole days).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function period(string $range, ?string $from = null, ?string $to = null): array
    {
        $today = now(self::TIMEZONE)->startOfDay();

        return match ($range) {
            '7d' => [$today->copy()->subDays(6), $today],
            'this_month' => [$today->copy()->startOfMonth(), $today],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()->startOfDay()],
            'this_year' => [$today->copy()->startOfYear(), $today],
            'last_12m' => [$today->copy()->subMonthsNoOverflow(11)->startOfMonth(), $today],
            'custom' => [
                Carbon::createFromFormat('Y-m-d', $from, self::TIMEZONE)->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $to, self::TIMEZONE)->startOfDay(),
            ],
            default => [$today->copy()->subDays(29), $today],   // 30d
        };
    }

    /** Orders that count as sales. */
    public static function salesQuery(): Builder
    {
        return Order::query()
            ->where('status', '!=', 'cancelled')
            ->where(fn (Builder $q) => $q->where('payment_method', '!=', 'mollie')->orWhere('payment_status', 'paid'));
    }

    /** Sales orders placed in [$from, $to] (whole local days). */
    private function orders(Carbon $from, Carbon $to): Collection
    {
        return self::salesQuery()
            ->whereBetween('created_at', [
                $from->copy()->startOfDay()->utc(),
                $to->copy()->endOfDay()->utc(),
            ])
            ->get(['id', 'created_at', 'subtotal', 'shipping_cost', 'total', 'vat_amount', 'discount_amount', 'coupon_code']);
    }

    /**
     * @return array{revenue: float, orders: int, average: float, vat: float, excl_vat: float, shipping: float, discounts: float}
     */
    private static function totals(Collection $orders): array
    {
        $revenue = round((float) $orders->sum(fn ($o) => (float) $o->total), 2);
        // Older orders have no stored VAT: work it out at today's rate.
        $vat = round((float) $orders->sum(fn ($o) => $o->vat_amount !== null ? (float) $o->vat_amount : Vat::included((float) $o->total)), 2);
        $count = $orders->count();

        return [
            'revenue' => $revenue,
            'orders' => $count,
            'average' => $count ? round($revenue / $count, 2) : 0.0,
            'vat' => $vat,
            'excl_vat' => round($revenue - $vat, 2),
            'shipping' => round((float) $orders->sum(fn ($o) => (float) $o->shipping_cost), 2),
            'discounts' => round((float) $orders->sum(fn ($o) => (float) $o->discount_amount), 2),
        ];
    }

    public function days(): int
    {
        // Rounded: a day with a clock change is 23 or 25 hours long.
        return (int) round(abs($this->from->diffInDays($this->to))) + 1;
    }

    public function isDaily(): bool
    {
        return $this->days() <= self::DAILY_UP_TO_DAYS;
    }

    /** The same number of days just before this period (for comparison). */
    public function previous(): self
    {
        $to = $this->from->copy()->subDay();

        return new self($to->copy()->subDays($this->days() - 1), $to);
    }

    /**
     * Everything the report page needs.
     *
     * @return array{totals: array, previous: array, change: array, buckets: list<array{key: string, start: Carbon, revenue: float, orders: int}>}
     */
    public function build(): array
    {
        $orders = $this->orders($this->from, $this->to);
        $totals = self::totals($orders);
        // Credit notes made in the period (refunds of returned items).
        $totals['refunds'] = round((float) \App\Models\OrderReturn::whereBetween('created_at', [
            $this->from->copy()->startOfDay()->utc(), $this->to->copy()->endOfDay()->utc(),
        ])->sum('total'), 2);
        $previousTotals = self::totals($this->orders($this->previous()->from, $this->previous()->to));

        $change = [];
        foreach (['revenue', 'orders', 'average'] as $key) {
            $change[$key] = $previousTotals[$key] > 0
                ? round(($totals[$key] - $previousTotals[$key]) / $previousTotals[$key] * 100, 1)
                : null;   // nothing to compare with
        }

        return [
            'totals' => $totals,
            'previous' => $previousTotals,
            'change' => $change,
            'buckets' => $this->buckets($orders),
        ];
    }

    /**
     * One bucket per day (or month), empty ones included, oldest first.
     */
    private function buckets(Collection $orders): array
    {
        $daily = $this->isDaily();
        $format = $daily ? 'Y-m-d' : 'Y-m';

        $grouped = $orders->groupBy(fn ($o) => $o->created_at->copy()->tz(self::TIMEZONE)->format($format));

        $buckets = [];
        $cursor = $daily ? $this->from->copy() : $this->from->copy()->startOfMonth();
        while ($cursor->lte($this->to)) {
            $key = $cursor->format($format);
            $group = $grouped->get($key, collect());
            $buckets[] = [
                'key' => $key,
                'start' => $cursor->copy(),
                'revenue' => round((float) $group->sum(fn ($o) => (float) $o->total), 2),
                'orders' => $group->count(),
            ];
            $daily ? $cursor->addDay() : $cursor->addMonthNoOverflow()->startOfMonth();
        }

        return $buckets;
    }
}
