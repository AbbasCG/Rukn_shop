<x-app-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8" style="font-family: 'Signika', sans-serif;" dir="{{ $dir }}">
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
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.confirmation.order_number') }}</dt>
                    <dd class="text-primary-dark font-semibold">{{ $order->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.shipping.name') }}</dt>
                    <dd class="text-primary-dark">{{ $order->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.details.shipping_info') }}</dt>
                    <dd class="text-primary-dark">{{ $order->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.shipping.address') }}</dt>
                    <dd class="text-primary-dark">{{ $order->address_line1 }} {{ $order->address_line2 }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-neutral-700">{{ __('orders.shipping.city') }}</dt>
                    <dd class="text-primary-dark">{{ $order->postal_code }} {{ $order->city }}, {{ $order->country }}</dd>
                </div>
            </dl>
            <div class="mt-4 border-t border-primary-gray pt-4 space-y-2">
                @foreach($order->orderItems as $item)
                <div class="flex items-center justify-between">
                    <div class="text-neutral-700">{{ $item->product->name }} × {{ $item->quantity }}</div>
                    <div class="text-primary-dark font-semibold">€{{ number_format($item->price * $item->quantity, 2, ',', '.') }}</div>
                </div>
                @endforeach
                <div class="flex items-center justify-between"><span class="text-neutral-700">{{ __('orders.summary.subtotal') }}</span><span class="text-primary-dark font-semibold">€{{ number_format($order->subtotal, 2, ',', '.') }}</span></div>
                <div class="flex items-center justify-between"><span class="text-neutral-700">{{ __('orders.summary.shipping') }}</span><span class="text-primary-dark font-semibold">€{{ number_format($order->shipping_cost, 2, ',', '.') }}</span></div>
                <div class="flex items-center justify-between text-base"><span class="text-primary-dark font-semibold">{{ __('orders.summary.total') }}</span><span class="text-primary-dark font-semibold">€{{ number_format($order->total, 2, ',', '.') }}</span></div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ route('products.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-primary-dark text-white font-semibold shadow-lg hover:bg-primary-dark/90 transition">{{ __('orders.actions.continue_shopping') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>