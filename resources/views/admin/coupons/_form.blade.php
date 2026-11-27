{{-- Shared fields of the create and edit forms. --}}
@php
    $field = 'w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-primary-dark focus:border-primary-dark/30 focus:outline-none focus:ring-2 focus:ring-primary-dark/10';
@endphp
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="code" class="block text-sm font-medium text-primary-dark mb-1">{{ __('coupons.admin.code') }}</label>
        <input id="code" name="code" required maxlength="40" dir="ltr" autocomplete="off"
               value="{{ old('code', $coupon->code ?? '') }}" placeholder="GRATIS" class="{{ $field }} uppercase font-mono">
        <p class="mt-1 text-xs text-primary-dark/50">{{ __('coupons.admin.code_hint') }}</p>
        @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="min_order_amount" class="block text-sm font-medium text-primary-dark mb-1">{{ __('coupons.admin.min_order') }}</label>
        <input id="min_order_amount" name="min_order_amount" type="number" min="0" step="0.01" dir="ltr"
               value="{{ old('min_order_amount', $coupon->min_order_amount ?? '') }}" placeholder="0.00" class="{{ $field }}">
        <p class="mt-1 text-xs text-primary-dark/50">{{ __('coupons.admin.min_order_hint') }}</p>
        @error('min_order_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="note" class="block text-sm font-medium text-primary-dark mb-1">{{ __('coupons.admin.note') }}</label>
        <input id="note" name="note" maxlength="255" value="{{ old('note', $coupon->note ?? '') }}" placeholder="{{ __('coupons.admin.note_placeholder') }}" class="{{ $field }}">
        @error('note')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
<label class="mt-4 inline-flex items-center gap-2 text-sm text-primary-dark">
    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked(old('is_active', $coupon->is_active ?? true))>
    {{ __('coupons.admin.active') }}
</label>
