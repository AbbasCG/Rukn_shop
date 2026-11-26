<x-app-layout>
    @php
        $status = strtolower((string) $order->status);
        $statusColors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-blue-100 text-blue-800',
            'refunded' => 'bg-red-100 text-red-800',
        ];
        $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
    @endphp

    <div class="bg-gray-50/70 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10 space-y-6 text-start">
            <div class="print:hidden">
                <a href="{{ r('orders.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-primary-dark shadow-sm transition hover:bg-gray-50">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>{{ __('orders.actions.back_to_orders') }}</span>
                </a>
            </div>

            <section class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 print:shadow-none print:border-gray-300">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-2xl md:text-3xl font-bold text-primary-dark">
                            {{ __('orders.table.order_number') }} #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                        </h1>
                        <p class="text-sm text-neutral-600">{{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>

                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-sm font-medium {{ $statusClass }}">
                        {{ __('orders.status.' . $order->status) }}
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-neutral-500">{{ __('orders.summary.total') }}</p>
                        <p class="mt-1 text-2xl font-bold text-primary-dark">{{ money($order->total) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-neutral-500">{{ __('orders.summary.subtotal') }}</p>
                        <p class="mt-1 text-lg font-semibold text-primary-dark">{{ money($order->subtotal) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-neutral-500">{{ __('orders.summary.shipping') }}</p>
                        <p class="mt-1 text-lg font-semibold text-primary-dark">{{ money($order->shipping_cost) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-neutral-500">{{ __('orders.summary.payment_method') }}</p>
                        <p class="mt-1 text-lg font-semibold text-primary-dark">{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h2 class="text-lg font-semibold text-primary-dark mb-3">{{ __('orders.details.shipping_info') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-neutral-500">{{ __('orders.shipping.name') }}</p>
                            <p class="font-medium text-primary-dark">{{ $order->name }}</p>
                        </div>
                        <div>
                            <p class="text-neutral-500">{{ __('orders.shipping.phone') }}</p>
                            <p class="font-medium text-primary-dark">{{ $order->phone ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-neutral-500">{{ __('orders.shipping.address') }}</p>
                            <p class="font-medium text-primary-dark">
                                {{ $order->address_line1 }}
                                @if($order->address_line2)
                                    <br>{{ $order->address_line2 }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-neutral-500">{{ __('orders.shipping.country') }}</p>
                            <p class="font-medium text-primary-dark">{{ $order->country }}</p>
                        </div>
                        <div>
                            <p class="text-neutral-500">{{ __('orders.shipping.city') }}</p>
                            <p class="font-medium text-primary-dark">{{ $order->city }}</p>
                        </div>
                        <div>
                            <p class="text-neutral-500">{{ __('orders.shipping.postal_code') }}</p>
                            <p class="font-medium text-primary-dark">{{ $order->postal_code }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 print:shadow-none print:border-gray-300">
                <h2 class="text-lg font-semibold text-primary-dark mb-4">{{ __('orders.details.items') }}</h2>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-neutral-600 border-b border-gray-200">
                                <th class="py-3 font-semibold text-start">{{ __('orders.details.items') }}</th>
                                <th class="py-3 font-semibold text-start">{{ __('orders.items.quantity') }}</th>
                                <th class="py-3 font-semibold text-start">{{ __('orders.items.price_per_unit') }}</th>
                                <th class="py-3 font-semibold text-start">{{ __('orders.items.item_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                @php $product = $item->product; @endphp
                                <tr class="border-b border-gray-100 last:border-0 transition hover:bg-gray-50/70">
                                    <td class="py-4">
                                        <div class="flex items-center gap-3">
                                            <img
                                                src="{{ optional($product)->primaryImageUrl() ?? asset(\App\Models\Product::FALLBACK_IMAGE) }}"
                                                alt="{{ optional($product)->name ?? 'Product' }}"
                                                class="w-14 h-14 rounded-lg object-cover border border-gray-200"
                                                onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">
                                            <div>
                                                @if($product)
                                                    <a href="{{ r('products.show', $product) }}" class="font-semibold text-primary-dark hover:text-primary-dark/70 transition">
                                                        {{ $product->name }}
                                                    </a>
                                                @else
                                                    <p class="font-semibold text-primary-dark">Product</p>
                                                @endif
                                                <p class="text-xs text-neutral-500 mt-1">{{ __('orders.items.sku') }}: {{ optional($product)->sku ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 font-medium text-primary-dark">{{ $item->quantity }}</td>
                                    <td class="py-4 font-medium text-primary-dark">{{ money($item->price) }}</td>
                                    <td class="py-4 font-semibold text-primary-dark">{{ money($item->quantity * $item->price) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-neutral-600">{{ __('orders.items.no_items') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="space-y-4 md:hidden">
                    @forelse($order->items as $item)
                        @php $product = $item->product; @endphp
                        <article class="rounded-xl border border-gray-200 p-4 bg-gray-50/60 transition hover:bg-gray-50">
                            <div class="flex gap-3">
                                <img
                                    src="{{ optional($product)->primaryImageUrl() ?? asset(\App\Models\Product::FALLBACK_IMAGE) }}"
                                    alt="{{ optional($product)->name ?? 'Product' }}"
                                    class="w-16 h-16 rounded-lg object-cover border border-gray-200"
                                    onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">

                                <div class="flex-1 min-w-0 space-y-3">
                                    <div>
                                        @if($product)
                                            <a href="{{ r('products.show', $product) }}" class="block font-semibold text-primary-dark hover:text-primary-dark/70 truncate transition">
                                                {{ $product->name }}
                                            </a>
                                        @else
                                            <p class="font-semibold text-primary-dark">Product</p>
                                        @endif
                                        <p class="text-xs text-neutral-500 mt-1">{{ __('orders.items.sku') }}: {{ optional($product)->sku ?? 'N/A' }}</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-neutral-500">{{ __('orders.items.quantity') }}</p>
                                            <p class="font-medium text-primary-dark">{{ $item->quantity }}</p>
                                        </div>
                                        <div>
                                            <p class="text-neutral-500">{{ __('orders.items.price_per_unit') }}</p>
                                            <p class="font-medium text-primary-dark">{{ money($item->price) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-200 flex items-center justify-between">
                                <p class="text-sm text-neutral-600">{{ __('orders.items.item_total') }}</p>
                                <p class="text-lg font-bold text-primary-dark">{{ money($item->quantity * $item->price) }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="text-center text-neutral-600 py-8">{{ __('orders.items.no_items') }}</p>
                    @endforelse
                </div>
            </section>

            @if($order->notes)
                <section class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 print:shadow-none print:border-gray-300">
                    <h2 class="text-lg font-semibold text-primary-dark mb-2">{{ __('orders.details.notes') }}</h2>
                    <p class="text-neutral-700">{{ $order->notes }}</p>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
