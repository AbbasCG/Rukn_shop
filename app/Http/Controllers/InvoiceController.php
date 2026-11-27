<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Invoices;
use Symfony\Component\HttpFoundation\Response;

/**
 * Invoice PDF downloads: for the customer from the private order link, and
 * for the admin. Only orders that have an invoice number have an invoice.
 */
class InvoiceController extends Controller
{
    public function show(string $locale, int $order, string $token): Response
    {
        $order = Order::with('items')->find($order);

        if (! $order || ! $order->access_token || ! hash_equals($order->access_token, $token) || ! $order->hasInvoice()) {
            abort(404);
        }

        return $this->download($order);
    }

    public function admin(string $locale, Order $order): Response
    {
        abort_unless($order->hasInvoice(), 404);

        return $this->download($order);
    }

    private function download(Order $order): Response
    {
        return response(Invoices::pdf($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . Invoices::filename($order) . '"',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
