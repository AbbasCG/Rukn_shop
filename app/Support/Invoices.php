<?php

namespace App\Support;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

/**
 * Invoices: numbering and the PDF.
 *
 * Numbers form one unbroken sequence per year (2026-0001…). An order gets
 * its number once, when it is paid online or first shipped / delivered;
 * cancelled orders never get one.
 */
class Invoices
{
    /**
     * Give the order an invoice number if it should have one and has none yet.
     * Safe to call more than once and from parallel requests.
     */
    public static function assign(Order $order): ?string
    {
        if ($order->invoice_number !== null) {
            return $order->invoice_number;
        }
        if (! self::isDue($order)) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            // Re-read under lock: another request may have numbered it meanwhile.
            $fresh = Order::whereKey($order->id)->lockForUpdate()->first();
            if ($fresh->invoice_number !== null) {
                $order->setRawAttributes($fresh->getAttributes(), true);

                return $fresh->invoice_number;
            }

            $year = (int) now()->format('Y');
            DB::table('invoice_counters')->insertOrIgnore(['year' => $year, 'last_number' => 0]);
            $counter = DB::table('invoice_counters')->where('year', $year)->lockForUpdate()->first();
            $next = (int) $counter->last_number + 1;
            DB::table('invoice_counters')->where('year', $year)->update(['last_number' => $next]);

            $number = sprintf('%d-%04d', $year, $next);
            $fill = ['invoice_number' => $number, 'invoiced_at' => now()];
            // Orders placed before VAT was stored: prices always included it.
            if ($fresh->vat_rate === null || $fresh->vat_amount === null) {
                $fill += ['vat_rate' => Vat::rate(), 'vat_amount' => Vat::included((float) $fresh->total)];
            }
            $fresh->forceFill($fill)->save();
            $order->forceFill($fresh->only(array_keys($fill)));

            return $number;
        });
    }

    /** Paid online, or shipped / delivered; never cancelled. */
    public static function isDue(Order $order): bool
    {
        if ($order->status === 'cancelled') {
            return false;
        }

        return $order->payment_status === 'paid'
            || in_array($order->status, ['shipped', 'delivered'], true);
    }

    /** Invoice language: Dutch for Dutch orders, otherwise English. */
    public static function locale(Order $order): string
    {
        return $order->locale === 'nl' ? 'nl' : 'en';
    }

    public static function filename(Order $order): string
    {
        return 'Rukn-' . (self::locale($order) === 'nl' ? 'factuur' : 'invoice') . '-' . $order->invoice_number . '.pdf';
    }

    /** The PDF as bytes, in the invoice language. */
    public static function pdf(Order $order): string
    {
        $order->loadMissing('items');
        $previous = app()->getLocale();
        app()->setLocale(self::locale($order));

        try {
            return Pdf::loadView('invoices.pdf', ['order' => $order, 'business' => config('shop.business')])
                ->setPaper('a4')
                ->output();
        } finally {
            app()->setLocale($previous);
        }
    }

    /** Business details a complete Dutch invoice needs but are not configured. */
    public static function missingBusinessDetails(): array
    {
        $business = config('shop.business');

        return array_values(array_filter([
            blank($business['address'] ?? null) ? 'SHOP_BUSINESS_ADDRESS' : null,
            blank($business['kvk'] ?? null) ? 'SHOP_BUSINESS_KVK' : null,
            blank($business['vat_id'] ?? null) ? 'SHOP_BUSINESS_VAT_ID' : null,
        ]));
    }
}
