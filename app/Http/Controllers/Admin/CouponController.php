<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Discount codes: list, create, edit, switch on/off, delete. */
class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::orderByDesc('is_active')->orderBy('code')->get();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        Coupon::create($this->validated($request));

        return redirect()->route('admin.coupons.index')->with('success', __('coupons.admin.created'));
    }

    public function edit(string $locale, Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, string $locale, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validated($request, $coupon));

        return redirect()->route('admin.coupons.index')->with('success', __('coupons.admin.updated'));
    }

    public function destroy(string $locale, Coupon $coupon): RedirectResponse
    {
        // Orders keep the code text they used; deleting only stops new use.
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', __('coupons.admin.deleted'));
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge(['code' => Coupon::normalize($request->input('code'))]);

        $data = $request->validate([
            'code' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'min_order_amount' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'code.regex' => __('coupons.admin.code_format'),
        ]);

        return $data + ['type' => 'free_shipping', 'is_active' => $request->boolean('is_active')];
    }
}
