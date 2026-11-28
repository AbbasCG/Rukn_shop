<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\OrderReturnController;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;

/**
 * Credit note PDFs for the customer, from the private order link.
 */
class CreditNoteController extends Controller
{
    public function show(string $locale, int $order, string $token, int $return): Response
    {
        $order = Order::find($order);

        if (! $order || ! $order->access_token || ! hash_equals($order->access_token, $token)) {
            abort(404);
        }

        $return = $order->returns()->whereKey($return)->firstOrFail();
        $return->setRelation('order', $order);

        return OrderReturnController::download($return);
    }
}
