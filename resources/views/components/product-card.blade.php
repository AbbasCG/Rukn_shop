@props(['product'])

@php
    $stock = (int) ($product->stock ?? 0);
    $soldOut = $stock <= 0;
    $lowStock = ! $soldOut && $stock <= 5;
    $url = r('products.show', $product);
@endphp

{{-- One product card for the whole storefront (home, shop, related products). --}}
<article {{ $attributes->merge(['class' => 'group flex flex-col']) }}>
    <a href="{{ $url }}" class="relative block aspect-[3/4] overflow-hidden rounded-2xl bg-primary-gray" tabindex="-1" aria-hidden="true">
        <img
            src="{{ $product->primaryImageUrl() }}"
            alt=""
            loading="lazy"
            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105 {{ $soldOut ? 'opacity-60 grayscale' : '' }}"
            onerror="this.onerror=null;this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'"
        >
        @if($soldOut)
            <span class="absolute top-3 start-3 rounded-lg bg-white/95 px-2.5 py-1 text-xs font-semibold text-primary-dark">
                {{ __('shop.out_of_stock') }}
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-1 pt-3">
        @if($product->category)
            <span class="text-xs text-neutral-500">{{ $product->category->display_name }}</span>
        @endif

        <h3 class="text-sm font-semibold leading-snug text-primary-dark line-clamp-2">
            <a href="{{ $url }}" class="hover:underline underline-offset-4"><bdi>{{ $product->name }}</bdi></a>
        </h3>

        <div class="mt-auto flex items-center justify-between gap-2 pt-1">
            <span class="text-base font-bold text-primary-dark">{{ money($product->price) }}</span>
            @if($lowStock)
                <span class="text-xs font-medium text-amber-700">{{ __('shop.low_stock', ['count' => $stock]) }}</span>
            @endif
        </div>
    </div>

    <div
        class="pt-3"
        x-data="addToCart({
            url: @js(r('cart.store')),
            productId: {{ (int) $product->id }},
            addedMessage: @js(__('shop.added')),
            errorMessage: @js(__('shop.add_failed')),
        })"
    >
        <button
            type="button"
            @click="add()"
            :disabled="loading || {{ $soldOut ? 'true' : 'false' }}"
            @disabled($soldOut)
            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-primary-dark text-sm font-semibold text-primary-dark transition-colors hover:bg-primary-dark hover:text-white disabled:cursor-not-allowed disabled:border-neutral-300 disabled:text-neutral-400 disabled:hover:bg-transparent"
        >
            <span x-show="!added">{{ $soldOut ? __('shop.out_of_stock') : __('shop.add_to_cart') }}</span>
            <span x-show="added" x-cloak>{{ __('shop.added') }}</span>
        </button>
    </div>
</article>
