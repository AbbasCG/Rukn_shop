@extends('layouts.admin', ['title' => __('coupons.admin.title')])

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-dark">{{ __('coupons.admin.title') }}</h1>
        <p class="text-sm text-primary-dark/60 mt-1">{{ __('coupons.admin.subtitle') }}</p>
    </div>

    <form method="POST" action="{{ r('admin.coupons.store') }}" class="bg-white rounded-xl shadow p-6">
        @csrf
        <h2 class="text-lg font-semibold text-primary-dark mb-4">{{ __('coupons.admin.new') }}</h2>
        @include('admin.coupons._form', ['coupon' => null])
        <div class="mt-5">
            <button type="submit" class="rounded-lg bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark/90">{{ __('coupons.admin.create') }}</button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-primary-dark/70">
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ __('coupons.admin.code') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('coupons.admin.kind') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('coupons.admin.min_order') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('coupons.admin.uses') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('coupons.admin.status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($coupons as $coupon)
                    <tr class="{{ $coupon->is_active ? '' : 'opacity-60' }}">
                        <td class="px-4 py-3 font-mono font-semibold text-primary-dark" dir="ltr">{{ $coupon->code }}
                            @if ($coupon->note)<div class="font-sans font-normal text-xs text-primary-dark/50">{{ $coupon->note }}</div>@endif
                        </td>
                        <td class="px-4 py-3">{{ __('coupons.kinds.' . $coupon->type) }}</td>
                        <td class="px-4 py-3">{{ $coupon->min_order_amount !== null ? money($coupon->min_order_amount) : '—' }}</td>
                        <td class="px-4 py-3">{{ $coupon->uses_count }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs {{ $coupon->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $coupon->is_active ? __('coupons.admin.on') : __('coupons.admin.off') }}</span>
                        </td>
                        <td class="px-4 py-3 text-end whitespace-nowrap">
                            <a href="{{ r('admin.coupons.edit', $coupon) }}" class="text-primary-dark underline underline-offset-2">{{ __('coupons.admin.edit') }}</a>
                            <form method="POST" action="{{ r('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm(@js(__('coupons.admin.delete_confirm')))">
                                @csrf @method('DELETE')
                                <button type="submit" class="ms-3 text-red-700 hover:underline">{{ __('coupons.admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-primary-dark/60">{{ __('coupons.admin.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
