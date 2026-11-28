@props(['amount'])
{{-- The regular price, crossed out next to a reduced price. --}}
<del {{ $attributes->merge(['class' => 'text-neutral-400 font-normal']) }}><span class="sr-only">{{ __('shop.sale.was') }}: </span>{{ $amount }}</del>
