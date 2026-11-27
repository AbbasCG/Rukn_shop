<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Support\CartTotals;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Entering / removing a discount code on the cart page. The code is kept in
 * the session; CartTotals applies it wherever totals are shown.
 */
class CouponController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:40']]);

        $coupon = Coupon::findByCode($request->input('code'));

        if (! $coupon || ! $coupon->is_active) {
            return back()->withInput()->withErrors(['code' => __('cart.coupon.invalid')], 'coupon');
        }

        session([CartTotals::SESSION_KEY => $coupon->code]);

        return back()->with('success', __('cart.coupon.applied', ['code' => $coupon->code]));
    }

    public function destroy(): RedirectResponse
    {
        session()->forget(CartTotals::SESSION_KEY);

        return back()->with('success', __('cart.coupon.removed'));
    }
}
