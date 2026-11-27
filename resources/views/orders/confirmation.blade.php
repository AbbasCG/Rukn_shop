<x-app-layout :title="__('seo.order_title', ['number' => $order->number])" :noindex="true">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
        <div class="rounded-3xl backdrop-blur-xl bg-white/70 shadow-xl border border-white/20 p-8 text-center">
            <div class="mx-auto w-16 h-16 rounded-full bg-primary-gray/60 flex items-center justify-center">
                <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-primary-dark">{{ __('orders.confirmation.thank_you') }}</h1>
            <p class="text-sm text-neutral-700">{{ __('orders.confirmation.received') }}</p>
        </div>
        <div class="rounded-3xl bg-white shadow-xl p-6">
            <h2 class="text-lg font-semibold text-primary-dark">{{ __('orders.confirmation.summary') }}</h2>
            <p class="mt-2 text-sm text-neutral-700">
                {!! __('checkout.confirmation.placed_with_number', ['number' => '<span class="font-bold text-primary-dark">#' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '</span>']) !!}
            </p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.confirmation.order_number') }}</dt>
                    <dd class="text-primary-dark font-semibold" dir="ltr">#{{ $order->number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.shipping.name') }}</dt>
                    <dd class="text-primary-dark"><bdi>{{ $order->name }}</bdi></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('checkout.form.email') }}</dt>
                    <dd class="text-primary-dark"><bdi>{{ $order->email }}</bdi></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.shipping.address') }}</dt>
                    <dd class="text-primary-dark"><bdi>{{ trim($order->address_line1 . ' ' . $order->address_line2) }}</bdi></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.shipping.city') }}</dt>
                    <dd class="text-primary-dark"><bdi>{{ $order->postal_code }} {{ $order->city }}, {{ $order->country }}</bdi></dd>
                </div>
            </dl>
            <div class="mt-4 border-t border-primary-gray pt-4 space-y-2">
                @foreach($order->orderItems as $item)
                <div class="flex items-center justify-between">
                    <div class="text-neutral-700"><bdi>{{ $item->full_name }}</bdi> × {{ $item->quantity }}</div>
                    <div class="text-primary-dark font-semibold">{{ money($item->price * $item->quantity) }}</div>
                </div>
                @endforeach
                <div class="flex items-center justify-between"><span class="text-neutral-700">{{ __('orders.summary.subtotal') }}</span><span class="text-primary-dark font-semibold">{{ money($order->subtotal) }}</span></div>
                <div class="flex items-center justify-between"><span class="text-neutral-700">{{ __('orders.summary.shipping') }}</span><span class="text-primary-dark font-semibold">{{ $order->shippingText() }}</span></div>
                <div class="flex items-center justify-between text-base"><span class="text-primary-dark font-semibold">{{ $order->hasVat() ? __('shop.vat.total_incl') : __('orders.summary.total') }}</span><span class="text-primary-dark font-semibold">{{ money($order->total) }}</span></div>
                @if ($order->hasVat())
                    <div class="flex items-center justify-between text-xs text-neutral-500"><span>{{ __('shop.vat.included', ['rate' => \App\Support\Vat::rateLabel((float) $order->vat_rate)]) }}</span><span>{{ money($order->vat_amount) }}</span></div>
                @endif
            </div>
            @if($order->isGuest())
                {{-- No account: tell the buyer how to get back to this order. --}}
                <div class="mt-6 rounded-xl bg-primary-50 border border-primary-200 p-4 text-sm text-primary-dark space-y-2 text-start">
                    <p>{{ __('orders.guest.save_link', ['email' => $order->email]) }}</p>
                    <p>{{ __('orders.guest.find_later') }} <a href="{{ r('orders.lookup') }}" class="font-semibold underline underline-offset-2">{{ __('orders.guest.find_link') }}</a></p>
                    <p class="text-neutral-600">{{ __('orders.guest.create_account') }}</p>
                </div>
            @endif
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-center gap-3">
                <a href="{{ $order->customerUrl() }}" class="btn-primary">
                    {{ __('checkout.confirmation.view_order') }}
                </a>
                <a href="{{ r('products.index') }}" class="inline-flex h-11 items-center justify-center px-6 rounded-xl bg-white border border-primary-gray text-primary-dark text-sm font-semibold shadow-sm hover:bg-primary-gray/30 transition">
                    {{ __('checkout.confirmation.continue_shopping') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
