<x-app-layout :title="__('seo.checkout_title')" :noindex="true">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <style>
            @media (prefers-reduced-motion: no-preference) {
                @keyframes fade-in-up {
                    from {
                        opacity: 0;
                        transform: translateY(12px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .animate-fade-in-up {
                    animation: fade-in-up 600ms ease-out both;
                }
            }
        </style>
        <div class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-8 animate-fade-in-up">
            <h1 class="text-2xl font-bold text-primary-dark">{{ __('checkout.title') }}</h1>
            <p class="text-sm text-neutral-600">{{ __('checkout.subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Customer info form -->
            <div class="lg:col-span-2 backdrop-blur-xl bg-white/70 shadow-xl rounded-3xl border border-white/20 p-8">
                <form method="POST" action="{{ r('checkout.store') }}" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="checkout-name" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.name') }}</label>
                            <input id="checkout-name" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="checkout-name-error" @enderror name="name" value="{{ old('name', auth()->user()->name ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                            @error('name')<p id="checkout-name-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="checkout-email" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.email') }}</label>
                            <input id="checkout-email" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="checkout-email-error" @enderror type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                            @error('email')<p id="checkout-email-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="checkout-phone" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.phone') }}</label>
                            <input id="checkout-phone" autocomplete="tel" type="tel" inputmode="tel" @error('phone') aria-invalid="true" aria-describedby="checkout-phone-error" @enderror name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                            @error('phone')<p id="checkout-phone-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="checkout-country" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.country') }}</label>
                            <input id="checkout-country" autocomplete="country-name" @error('country') aria-invalid="true" aria-describedby="checkout-country-error" @enderror name="country" value="{{ old('country', auth()->user()->country ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                            @error('country')<p id="checkout-country-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="checkout-address_line1" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.address1') }}</label>
                            <input id="checkout-address_line1" autocomplete="address-line1" @error('address_line1') aria-invalid="true" aria-describedby="checkout-address_line1-error" @enderror name="address_line1" value="{{ old('address_line1', auth()->user()->address_line1 ?? auth()->user()->address ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                            @error('address_line1')<p id="checkout-address_line1-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="checkout-address_line2" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.address2') }}</label>
                            <input id="checkout-address_line2" autocomplete="address-line2" @error('address_line2') aria-invalid="true" aria-describedby="checkout-address_line2-error" @enderror name="address_line2" value="{{ old('address_line2', auth()->user()->address_line2 ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" />
                            @error('address_line2')<p id="checkout-address_line2-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="checkout-postal_code" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.postal_code') }}</label>
                            <input id="checkout-postal_code" autocomplete="postal-code" @error('postal_code') aria-invalid="true" aria-describedby="checkout-postal_code-error" @enderror name="postal_code" value="{{ old('postal_code', auth()->user()->postal_code ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                            @error('postal_code')<p id="checkout-postal_code-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="checkout-city" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.city') }}</label>
                            <input id="checkout-city" autocomplete="address-level2" @error('city') aria-invalid="true" aria-describedby="checkout-city-error" @enderror name="city" value="{{ old('city', auth()->user()->city ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                            @error('city')<p id="checkout-city-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="checkout-shipping_method" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.shipping_method') }}</label>
                            <select id="checkout-shipping_method" @error('shipping_method') aria-invalid="true" aria-describedby="checkout-shipping_method-error" @enderror name="shipping_method" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required>
                                <option value="flat">{{ __('checkout.form.shipping_options.flat') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="checkout-payment_method" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.payment_method') }}</label>
                            <select id="checkout-payment_method" @error('payment_method') aria-invalid="true" aria-describedby="checkout-payment_method-error" @enderror name="payment_method" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required>
                                <option value="cod">{{ __('checkout.form.payment_options.cod') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="btn-primary">{{ __('checkout.form.submit') }}</button>
                    </div>
                </form>
            </div>

            <!-- Right: Order summary -->
            <div class="space-y-6">
                <div class="bg-white shadow-xl rounded-3xl p-6">
                    <h2 class="text-lg font-semibold text-primary-dark">{{ __('checkout.summary.title') }}</h2>
                    <div class="mt-4 space-y-4">
                        @foreach($cartItems as $item)
                        @php
                        $qty = (int) ($item->quantity ?? 1);
                        $price = $item->unitPrice();
                        $variantLabel = $item->variant?->label();
                        @endphp
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm text-neutral-700">
                                <bdi>{{ $item->product->name }}</bdi> × {{ $qty }}
                                @if($variantLabel)<span class="block text-xs text-neutral-500"><bdi>{{ $variantLabel }}</bdi></span>@endif
                            </div>
                            <div class="text-sm font-semibold text-primary-dark">{{ money($price * $qty) }}</div>
                        </div>
                        @endforeach
                        <div class="border-t border-primary-gray my-2"></div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-700">{{ __('checkout.summary.subtotal') }}</span>
                            <span class="font-semibold text-primary-dark">{{ money($subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-700">{{ __('checkout.summary.shipping') }}</span>
                            <span class="font-semibold text-primary-dark">{{ $shipping > 0 ? money($shipping) : __('cart.summary.free') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-base">
                            <span class="text-primary-dark font-semibold">{{ __('checkout.summary.total') }}</span>
                            <span class="text-primary-dark font-semibold">{{ money($total) }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ r('cart.index') }}" class="inline-flex items-center px-5 py-3 rounded-xl border border-primary-gray text-primary-dark hover:bg-primary-gray transition">{{ __('checkout.summary.back_to_cart') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
