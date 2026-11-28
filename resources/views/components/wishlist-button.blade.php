@props(['product', 'size' => 'md'])
{{-- Heart: add to / remove from favourites (works for guests too). --}}
@php
    $saved = \App\Support\Wishlist::has($product->id);
    $box = $size === 'sm' ? 'h-9 w-9' : 'h-11 w-11';
    $icon = $size === 'sm' ? 'h-5 w-5' : 'h-6 w-6';
@endphp
<button type="button"
    x-data="wishlistButton({ url: @js(r('wishlist.toggle', $product)), saved: @js($saved), errorMessage: @js(__('shop.add_failed')) })"
    @click.prevent="toggle()" @wishlist-changed.window="sync($event)"
    :aria-pressed="saved.toString()" aria-pressed="{{ $saved ? 'true' : 'false' }}"
    :title="saved ? @js(__('wishlist.remove')) : @js(__('wishlist.add'))" title="{{ $saved ? __('wishlist.remove') : __('wishlist.add') }}"
    :aria-label="saved ? @js(__('wishlist.remove')) : @js(__('wishlist.add'))" aria-label="{{ $saved ? __('wishlist.remove') : __('wishlist.add') }}"
    {{ $attributes->merge(['class' => "inline-flex {$box} items-center justify-center rounded-full bg-white/95 text-primary-dark shadow-sm ring-1 ring-black/5 transition hover:scale-105"]) }}>
    <svg class="{{ $icon }}" :class="saved ? 'fill-red-600 text-red-600' : 'fill-transparent'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
    </svg>
</button>
