<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * "Email me when it is back" on the product page.
 */
class StockNotificationController extends Controller
{
    public function store(Request $request, string $locale, Product $product): RedirectResponse|JsonResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'variant_id' => ['nullable', 'integer'],
        ]);

        $product->loadMissing('variants');
        $variant = null;
        if ($product->hasVariants()) {
            $variant = $product->variants->firstWhere('id', (int) ($data['variant_id'] ?? 0));
            if (! $variant) {
                return $this->answer($request, false, __('product.notify.choose_variant'));
            }
        }

        $stock = $variant ? (int) $variant->stock : (int) $product->stock;
        if ($stock > 0) {
            return $this->answer($request, false, __('product.notify.in_stock'));
        }

        $email = Str::lower(trim($data['email']));
        $existing = StockNotification::pending()
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->where('email', $email)
            ->exists();

        if ($existing) {
            return $this->answer($request, true, __('product.notify.already'));
        }

        StockNotification::create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'user_id' => $request->user()?->id,
            'email' => $email,
            'locale' => app()->getLocale(),
        ]);

        return $this->answer($request, true, __('product.notify.done', ['email' => $email]));
    }

    private function answer(Request $request, bool $ok, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'warning', $message);
    }
}
