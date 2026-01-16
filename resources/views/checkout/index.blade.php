<x-app-layout>
    @php
    $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10" style="font-family: 'Signika', sans-serif;" dir="{{ $dir }}">
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
                <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.name') }}</label>
                            <input name="name" value="{{ old('name', auth()->user()->name ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.email') }}</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.phone') }}</label>
                            <input name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.country') }}</label>
                            <input name="country" value="{{ old('country', auth()->user()->country ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.address1') }}</label>
                            <input name="address_line1" value="{{ old('address_line1', auth()->user()->address_line1 ?? auth()->user()->address ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.address2') }}</label>
                            <input name="address_line2" value="{{ old('address_line2', auth()->user()->address_line2 ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.postal_code') }}</label>
                            <input name="postal_code" value="{{ old('postal_code', auth()->user()->postal_code ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.city') }}</label>
                            <input name="city" value="{{ old('city', auth()->user()->city ?? '') }}" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.shipping_method') }}</label>
                            <select name="shipping_method" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required>
                                <option value="flat">{{ __('checkout.form.shipping_options.flat') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('checkout.form.payment_method') }}</label>
                            <select name="payment_method" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" required>
                                <option value="cod">{{ __('checkout.form.payment_options.cod') }}</option>
                                <option value="mock">{{ __('checkout.form.payment_options.mock') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-primary-dark text-white font-semibold shadow-lg hover:bg-primary-dark/90 transition">{{ __('checkout.form.submit') }}</button>
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
                        $qtyCol = isset($item->quantity) ? 'quantity' : (property_exists($item, 'quanttty') ? 'quanttty' : 'quantity');
                        $qty = $item->$qtyCol ?? 1;
                        $price = $item->price_at_time ?? $item->product->price;
                        @endphp
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-neutral-700">{{ $item->product->name }} × {{ $qty }}</div>
                            <div class="text-sm font-semibold text-primary-dark">€{{ number_format($price * $qty, 2, ',', '.') }}</div>
                        </div>
                        @endforeach
                        <div class="border-t border-primary-gray my-2"></div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-700">{{ __('checkout.summary.subtotal') }}</span>
                            <span class="font-semibold text-primary-dark">€{{ number_format($subtotal, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-700">{{ __('checkout.summary.shipping') }}</span>
                            <span class="font-semibold text-primary-dark">€{{ number_format($shipping, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-base">
                            <span class="text-primary-dark font-semibold">{{ __('checkout.summary.total') }}</span>
                            <span class="text-primary-dark font-semibold">€{{ number_format($total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('cart.index') }}" class="inline-flex items-center px-5 py-3 rounded-xl border border-primary-gray text-primary-dark hover:bg-primary-gray transition">{{ __('checkout.summary.back_to_cart') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>