{{-- Back from Mollie without a confirmed payment: processing, failed, or expired. --}}
@php
    $state = $pending ? 'pending' : ($order->status === 'cancelled' ? 'expired' : 'failed');
@endphp
@if($pending)
    @push('structured-data')
        {{-- The bank is still confirming: look again in a few seconds. --}}
        <meta http-equiv="refresh" content="5">
    @endpush
@endif
<x-app-layout :title="__('checkout.payment.' . $state . '_title')" :noindex="true">
    <div class="max-w-lg mx-auto px-4 sm:px-6 py-16 text-center">
        <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-full
            {{ $state === 'pending' ? 'bg-amber-100 text-amber-700' : ($state === 'failed' ? 'bg-red-100 text-red-700' : 'bg-neutral-200 text-neutral-700') }}">
            @if($state === 'pending')
                <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            @else
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            @endif
        </div>

        <h1 class="text-2xl font-bold text-primary-dark">{{ __('checkout.payment.' . $state . '_title') }}</h1>
        <p class="mt-3 text-neutral-600 leading-relaxed">{{ __('checkout.payment.' . $state . '_text') }}</p>
        <p class="mt-2 text-sm text-neutral-500" dir="ltr">#{{ $order->number }} · {{ money($order->total) }}</p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            @if($canRetry)
                <form method="POST" action="{{ r('payments.retry', ['order' => $order->id, 'token' => $order->access_token]) }}">
                    @csrf
                    <button type="submit" class="btn-primary">{{ __('checkout.payment.retry') }}</button>
                </form>
            @endif
            @unless($pending)
                <a href="{{ r('products.index') }}" class="btn-secondary">{{ __('checkout.payment.back_to_shop') }}</a>
            @endunless
        </div>
    </div>
</x-app-layout>
