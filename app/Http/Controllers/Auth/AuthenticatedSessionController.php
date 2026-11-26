<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $request->authenticate();

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
        $quantityColumn = Schema::hasColumn('carts', 'quantity') ? 'quantity' : 'quanttty';

        foreach ($guestCartItems as $productId => $rawQuantity) {
            $quantity = max(1, (int) $rawQuantity);

            $product = Product::query()
                ->whereKey($productId)
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->first();

            if (! $product) {
                $skipped++;
                continue;
            }

            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->first();

            if ($cartItem) {
                // Never merge beyond what is in stock.
                $cartItem->$quantityColumn = min(($cartItem->$quantityColumn ?? 0) + $quantity, $product->stock);
                $cartItem->price_at_time = $cartItem->price_at_time ?? $product->price;
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => min($quantity, $product->stock),
                    'status' => 'active',
                    'price_at_time' => $product->price,
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
