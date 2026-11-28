@props(['product'])
{{-- "-20%" (or "up to -20%") while a reduction runs; nothing otherwise. --}}
@php
    $discount = $product->discount();
    // The number stays left-to-right inside Arabic text.
    $badge = '<bdi dir="ltr">' . e(__('shop.sale.badge', ['percent' => (int) $discount['percent']])) . '</bdi>';
    $label = $discount['upTo'] ? str_replace('%BADGE%', $badge, e(__('shop.sale.up_to', ['badge' => '%BADGE%']))) : $badge;
@endphp
@if ($discount['percent'] > 0)
    <span {{ $attributes->merge(['class' => 'rounded-lg bg-red-700 px-2.5 py-1 text-xs font-bold text-white shadow-sm']) }}>{!! $label !!}</span>
@endif
