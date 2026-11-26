<x-app-layout :title="__('seo.cart_title')" :noindex="true">
    @php
    $qtyCol = \Illuminate\Support\Facades\Schema::hasColumn('carts','quantity') ? 'quantity' : 'quanttty';
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 min-h-screen">
        @if($cartItems->isEmpty())
        <div class="text-center py-16">
            <div class="mx-auto w-24 h-24 rounded-full bg-primary-gray/60 flex items-center justify-center shadow-lg">
                <svg class="w-12 h-12 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h2 class="mt-6 text-2xl font-bold text-primary-dark">{{ __('cart.empty.title') }}</h2>
            <p class="mt-2 text-neutral-700">{{ __('cart.empty.subtitle') }}</p>
            <a href="{{ r('products.index') }}" class="btn-primary mt-6">{{ __('cart.actions.continue') }}</a>
        </div>
        @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Cart Items -->
            <div class="lg:col-span-2 space-y-6">
                @foreach($cartItems as $item)
                @php
                $product = $item->product;
                $imageUrl = $product->primaryImageUrl();
                $price = $item->price_at_time ?? ($product->price ?? 0);
                $qty = $item->$qtyCol ?? 1;
                $itemTotal = $price * $qty;
                @endphp
                <div x-data="{ qty: {{ $qty }}, glow: false }" class="rounded-3xl backdrop-blur-xl bg-white/70 shadow-lg border border-white/20 p-6 flex gap-6 items-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-24 h-24 rounded-2xl object-cover border border-white/30" onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-primary-dark">{{ $product->name }}</h3>
                        <div class="mt-1 text-neutral-700">{{ money($price) }}</div>
                        <div class="mt-4 flex items-center gap-3">
                            @auth
                            <form method="POST" action="{{ r('cart.update', ['cart' => $item->id]) }}" x-ref="form">
                                @csrf
                                @method('PATCH')
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="qty = Math.max(1, qty-1); $refs.q.value = qty; $refs.form.submit(); glow=true; setTimeout(()=>glow=false,600)" class="w-9 h-9 flex items-center justify-center rounded-lg border border-primary-gray bg-white text-primary-dark hover:bg-primary-gray transition">–</button>
                                    <input x-ref="q" type="hidden" name="quantity" :value="qty">
                                    <span class="px-3 py-1 rounded-lg bg-primary-gray text-primary-dark text-sm">x <span x-text="qty"></span></span>
                                    <button type="button" @click="if (qty >= {{ (int) $product->stock }}) return; qty = qty+1; $refs.q.value = qty; $refs.form.submit(); glow=true; setTimeout(()=>glow=false,600)" @disabled($qty >= $product->stock) class="w-9 h-9 flex items-center justify-center rounded-lg border border-primary-gray bg-white text-primary-dark hover:bg-primary-gray transition disabled:opacity-40 disabled:cursor-not-allowed">+</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ r('cart.destroy', ['cart' => $item->id]) }}" x-data="{ removing: false }" @submit="removing = true">
                                @csrf
                                @method('DELETE')
                                <button type="submit" :disabled="removing" class="ms-4 inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed" title="{{ __('cart.item.remove') }}">
                                    <svg x-show="!removing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                    </svg>
                                    <svg x-show="removing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </button>
                            </form>
                            @else
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-lg bg-primary-gray text-primary-dark text-sm">x <span x-text="qty"></span></span>
                            </div>
                            @endauth
                        </div>
                    </div>
                    <div :class="glow ? 'bg-primary-gray/50' : ''" class="rounded-xl px-4 py-2 text-primary-dark font-semibold transition">
                        {{ money($itemTotal) }}
                    </div>
                </div>
                @endforeach
                @auth
                <form method="POST" action="{{ r('cart.clear') }}" class="pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl border border-primary-gray text-primary-dark bg-white hover:bg-primary-gray transition">{{ __('cart.actions.clear') }}</button>
                </form>
                @endauth
            </div>

            <!-- Right: Order Summary -->
            <div class="rounded-3xl bg-primary-dark text-white shadow-2xl p-8 space-y-6">
                <h2 class="text-xl font-bold">{{ __('cart.summary.title') }}</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between"><span>{{ __('cart.summary.subtotal') }}</span><span>{{ money($subtotal) }}</span></div>
                    <div class="flex items-center justify-between"><span>{{ __('cart.summary.shipping') }}</span><span>{{ $shipping > 0 ? money($shipping) : __('cart.summary.free') }}</span></div>
                    <div class="flex items-center justify-between text-base font-semibold"><span>{{ __('cart.summary.total') }}</span><span>{{ money($total) }}</span></div>
                </div>
                @php($remainingForFree = \App\Support\Shipping::remainingForFree((float) $subtotal))
                @if($remainingForFree > 0)
                    <p class="text-sm text-white/80">{{ __('cart.summary.free_shipping_hint', ['amount' => money($remainingForFree)]) }}</p>
                @endif
                @auth
                <a href="{{ r('checkout.index') }}" class="block w-full px-6 py-4 rounded-2xl bg-white text-primary-dark font-semibold shadow-lg hover:bg-white/80 transition text-center">{{ __('cart.actions.proceed') }}</a>
                @else
                <a href="{{ r('login') }}" class="block w-full px-6 py-4 rounded-2xl bg-white text-primary-dark font-semibold shadow-lg hover:bg-white/80 transition text-center">{{ __('cart.actions.signin') }}</a>
                @endauth
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
