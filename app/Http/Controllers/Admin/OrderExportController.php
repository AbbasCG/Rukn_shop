<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Excel export of orders for the bookkeeping: one sheet with an order per
 * row (amounts, VAT, invoice number), one with the order lines.
 */
class OrderExportController extends Controller
{
    public function download(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', 'in:all,pending,processing,shipped,delivered,cancelled'],
        ]);

        $from = Carbon::parse($validated['from'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now()->toDateString())->endOfDay();
        $status = $validated['status'] ?? 'all';

        $orders = Order::with('items')
            ->whereBetween('created_at', [$from, $to])
            ->when($status === 'all', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('created_at')
            ->get();

        // Accounting files: Dutch headers for a Dutch admin, otherwise English.
        $previous = app()->getLocale();
        app()->setLocale($previous === 'nl' ? 'nl' : 'en');

        try {
            $path = $this->write($orders);
        } finally {
            app()->setLocale($previous);
        }

        $name = sprintf('rukn-orders-%s_%s.xlsx', $from->toDateString(), $to->toDateString());

        return response()->download($path, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    private function write($orders): string
    {
        $path = tempnam(sys_get_temp_dir(), 'rukn-export-') . '.xlsx';
        $bold = (new Style())->withFontBold(true);
        $money = (new Style())->withFormat('#,##0.00');
        $date = (new Style())->withFormat('yyyy-mm-dd');
        $col = fn (string $key) => __('invoices.export.columns.' . $key);

        $writer = new Writer();
        $writer->openToFile($path);

        // Sheet 1: orders.
        $writer->getCurrentSheet()->setName(substr($col('sheet_orders'), 0, 31));
        $writer->addRow(Row::fromValuesWithStyle(array_map($col, [
            'order', 'invoice', 'order_date', 'invoice_date', 'status', 'payment_method', 'payment_status', 'paid_at',
            'customer', 'email', 'postal_code', 'city', 'country',
            'subtotal', 'shipping', 'total_incl', 'vat_rate', 'vat', 'total_excl', 'coupon', 'discount',
        ]), $bold));

        foreach ($orders as $order) {
            $vat = $order->vat_amount !== null ? (float) $order->vat_amount : null;
            $writer->addRow(Row::fromValuesWithStyles([
                $order->number,
                $order->invoice_number,
                $order->created_at?->toDateTimeImmutable(),
                $order->invoiced_at?->toDateTimeImmutable(),
                $order->status,
                $order->payment_method,
                $order->payment_status,
                $order->paid_at?->toDateTimeImmutable(),
                $order->name,
                $order->email,
                $order->postal_code,
                $order->city,
                $order->country,
                (float) $order->subtotal,
                (float) $order->shipping_cost,
                (float) $order->total,
                $order->vat_rate !== null ? (float) $order->vat_rate : null,
                $vat,
                $vat !== null ? round((float) $order->total - $vat, 2) : null,
                $order->coupon_code,
                (float) $order->discount_amount,
            ], [2 => $date, 3 => $date, 7 => $date, 13 => $money, 14 => $money, 15 => $money, 17 => $money, 18 => $money, 20 => $money]));
        }

        // Sheet 2: order lines.
        $writer->addNewSheetAndMakeItCurrent()->setName(substr($col('sheet_items'), 0, 31));
        $writer->addRow(Row::fromValuesWithStyle(array_map($col, [
            'order', 'invoice', 'order_date', 'product', 'quantity', 'unit_price', 'line_total',
        ]), $bold));

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $writer->addRow(Row::fromValuesWithStyles([
                    $order->number,
                    $order->invoice_number,
                    $order->created_at?->toDateTimeImmutable(),
                    $item->full_name,
                    (int) $item->quantity,
                    (float) $item->price,
                    round((float) $item->price * (int) $item->quantity, 2),
                ], [2 => $date, 5 => $money, 6 => $money]));
            }
        }

        $writer->close();

        return $path;
    }
}
