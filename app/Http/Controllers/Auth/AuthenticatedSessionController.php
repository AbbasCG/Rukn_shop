<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Support\GuestCart;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $guestCartItems = collect($request->session()->get('cart.items', []));

        $user = $request->checkCredentials();

        // Admins with two-step verification: password is right, now the
        // code from their email (TwoFactorChallengeController logs them in).
        if ($user->usesTwoFactor()) {
            try {
                TwoFactor::send($user, 'login', ['remember' => $request->boolean('remember')]);
            } catch (\Throwable $e) {
                Log::error('Two-step verification code could not be emailed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

                return back()->onlyInput('email')->withErrors(['email' => __('two_factor.send_failed')]);
            }

            return redirect()->route('two-factor.challenge');
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));

        $mergeResult = $this->mergeGuestCartIntoUserCart($request, $guestCartItems);
        $flashMessages = [];

        $request->session()->regenerate();

        if ($mergeResult['merged'] > 0) {
            $flashMessages['success'] = __('cart.flash.restored');
        }

        if ($mergeResult['skipped'] > 0) {
            $flashMessages['warning'] = __('cart.flash.restored_partial');
        }

        $user = $request->user();

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with($flashMessages);
        }

        return redirect()->intended(route('home'))->with($flashMessages);
    }

    private function mergeGuestCartIntoUserCart(Request $request, \Illuminate\Support\Collection $guestCartItems): array
    {
        $user = $request->user();

        if (! $user || $guestCartItems->isEmpty()) {
            return ['merged' => 0, 'skipped' => 0];
        }

        $merged = 0;
        $skipped = 0;

        // Keys are "<productId>" or "<productId>:<variantId>" (see GuestCart).
        foreach ($guestCartItems as $key => $rawQuantity) {
            $quantity = max(1, (int) $rawQuantity);
            [$productId, $variantId] = GuestCart::parseKey($key);

            $product = Product::with('variants')->whereKey($productId)->where('is_active', true)->first();
            $variant = $product && $variantId ? $product->variants->firstWhere('id', $variantId) : null;

            // Skip what can no longer be bought: hidden/deleted products, a missing
            // or foreign variant, or a product that now requires a size/colour.
            $valid = $product && ($product->hasVariants() ? $variant !== null : $variantId === null);
            $stock = $valid ? (int) ($variant ? $variant->stock : $product->stock) : 0;

            if ($stock <= 0) {
                $skipped++;
                continue;
            }

            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('variant_id', $variant?->id)
                ->where('status', 'active')
                ->first();

            $price = $variant ? $variant->setRelation('product', $product)->effectivePrice() : $product->currentPrice();

            if ($cartItem) {
                // Never merge beyond what is in stock.
                $cartItem->quantity = min(($cartItem->quantity ?? 0) + $quantity, $stock);
                $cartItem->price_at_time = $cartItem->price_at_time ?? $price;
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity' => min($quantity, $stock),
                    'status' => 'active',
                    'price_at_time' => $price,
                ]);
            }

            $merged++;
        }

        $request->session()->forget('cart.items');
        $request->session()->forget('cart.count');

        return ['merged' => $merged, 'skipped' => $skipped];
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Stay in the visitor's language instead of falling back to /nl.
        return redirect()->route('home');
    }
}
