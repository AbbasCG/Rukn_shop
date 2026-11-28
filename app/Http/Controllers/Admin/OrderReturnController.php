<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\RefundProcessed;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Support\CreditNotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns registered by the admin, their credit notes and refunds.
 */
class OrderReturnController extends Controller
{
    public function store(Request $request, string $locale, Order $order)
    {
        $validated = $request->validateWithBag('return', [
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'refund_shipping' => ['nullable', 'boolean'],
            'restock' => ['nullable', 'boolean'],
            'refunded' => ['nullable', 'boolean'],
            'notify' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $return = CreditNotes::create(
                $order,
                array_map('intval', $validated['quantities'] ?? []),
                $request->boolean('refund_shipping'),
                $request->boolean('restock'),
                $validated['reason'] ?? null,
                $request->boolean('refunded'),
                $request->user()->id,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $e->errorBag = 'return';
            throw $e;
        }

        if ($return->isRefunded() && $request->boolean('notify')) {
            $this->notify($return);
        }

        return redirect()->to(route('admin.orders.show', $order) . '#returns')
            ->with('success', __('returns.flash.created', ['number' => $return->credit_note_number, 'amount' => money($return->total)]));
    }

    public function refunded(Request $request, string $locale, OrderReturn $return)
    {
        if (! $return->isRefunded()) {
            $return->forceFill(['refunded_at' => now()])->save();
            if ($request->boolean('notify', true)) {
                $this->notify($return);
            }
        }

        return redirect()->to(route('admin.orders.show', $return->order_id) . '#returns')
            ->with('success', __('returns.flash.refunded', ['number' => $return->credit_note_number]));
    }

    public function pdf(string $locale, OrderReturn $return): Response
    {
        return self::download($return);
    }

    public static function download(OrderReturn $return): Response
    {
        return response(CreditNotes::pdf($return), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . CreditNotes::filename($return) . '"',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /** "Your refund has been made", with the credit note attached. */
    private function notify(OrderReturn $return): void
    {
        try {
            Mail::to($return->order->email)->queue(new RefundProcessed($return));
        } catch (\Throwable $e) {
            Log::error('Refund email could not be queued.', ['return_id' => $return->id, 'error' => $e->getMessage()]);
        }
    }
}
