@extends('layouts.admin', ['title' => __('admin.orders.show.page_title', ['id' => $order->id])])

@section('content')
@php
    $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $isRtl = $dir === 'rtl';
    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'processing' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'delivered' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'shipped' => 'bg-blue-100 text-blue-800',
    ];
    $statusLabel = function (string $status): string {
        $key = 'admin.orders.status.' . $status;
        $translated = __($key);
        return $translated !== $key ? $translated : ucfirst($status);
    };
    $statusClass = $statusColors[strtolower((string) $order->status)] ?? 'bg-gray-100 text-gray-800';

    $subtotal = (float) $order->orderItems->sum(fn($item) => $item->price * $item->quantity);
    $shipping = (float) ($order->shipping_cost ?? 0);
    $discount = (float) ($order->discount_amount ?? 0);
    $tax = (float) ($order->tax_amount ?? 0);
    $grandTotal = (float) ($order->total ?? ($subtotal + $shipping + $tax - $discount));
@endphp

<div class="w-full max-w-7xl mx-auto space-y-6" dir="{{ $dir }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ r('admin.orders.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-primary-dark hover:bg-gray-50 transition">
            <svg class="w-4 h-4 {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('admin.orders.actions.back_to_orders') }}
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-primary-dark hover:bg-gray-50 transition print:hidden">
            {{ __('admin.orders.actions.print') }}
        </button>
    </div>

    <section class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-2">
                <h1 class="text-2xl md:text-3xl font-bold text-primary-dark">{{ __('admin.orders.show.heading', ['number' => str_pad($order->id, 6, '0', STR_PAD_LEFT)]) }}</h1>
                <p class="text-sm text-primary-dark/60">{{ __('admin.orders.show.placed_on', ['date' => $order->created_at->translatedFormat('M d, Y H:i')]) }}</p>
                <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $statusClass }}">{{ $statusLabel(strtolower((string) $order->status)) }}</span>
            </div>

            <div class="text-start lg:text-end">
                <p class="text-xs uppercase tracking-wide text-primary-dark/60">{{ __('admin.orders.table.total') }}</p>
                <p class="text-3xl font-bold text-primary-dark">€{{ number_format($grandTotal, 2) }}</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <article class="lg:col-span-2 rounded-xl border border-gray-200 bg-gray-50/60 p-5">
                <h2 class="text-lg font-semibold text-primary-dark mb-4">{{ __('admin.orders.show.sections.customer_info') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-primary-dark/60">{{ __('admin.orders.show.fields.name') }}</p>
                        <p class="font-medium text-primary-dark">{{ $order->user->name ?? $order->name ?? __('admin.orders.labels.guest') }}</p>
                    </div>
                    <div>
                        <p class="text-primary-dark/60">{{ __('admin.orders.show.fields.email') }}</p>
                        <p class="font-medium text-primary-dark">{{ $order->user->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-primary-dark/60">{{ __('admin.orders.show.fields.phone') }}</p>
                        <p class="font-medium text-primary-dark">{{ $order->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-primary-dark/60">{{ __('admin.orders.show.fields.payment_method') }}</p>
                        <p class="font-medium text-primary-dark">{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-primary-dark/60">{{ __('admin.orders.show.fields.address') }}</p>
                        <p class="font-medium text-primary-dark">
                            {{ $order->shipping_address ?? $order->address_line1 ?? __('admin.orders.labels.not_specified') }}
                            @if(!empty($order->address_line2))
                                <br>{{ $order->address_line2 }}
                            @endif
                            @if(!empty($order->city) || !empty($order->postal_code) || !empty($order->country))
                                <br>{{ trim(($order->postal_code ?? '') . ' ' . ($order->city ?? '')) }}{{ !empty($order->country) ? ', ' . $order->country : '' }}
                            @endif
                        </p>
                    </div>
                </div>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="text-lg font-semibold text-primary-dark mb-4">{{ __('admin.orders.show.sections.update_status') }}</h2>
                <form action="{{ r('admin.orders.update', $order) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-primary-dark focus:border-primary-dark/30 focus:outline-none focus:ring-2 focus:ring-primary-dark/10">
                        @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ $statusLabel($status) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full rounded-lg bg-primary-dark px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark/90 transition">
                        {{ __('admin.orders.actions.save_status') }}
                    </button>
                </form>
            </article>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8">
            <h2 class="text-lg font-semibold text-primary-dark mb-4">{{ __('admin.orders.show.sections.order_items') }}</h2>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-primary-dark/70">
                            <th class="text-start py-3 font-semibold">{{ __('admin.orders.show.table.product') }}</th>
                            <th class="text-start py-3 font-semibold">{{ __('admin.orders.show.table.qty') }}</th>
                            <th class="text-start py-3 font-semibold">{{ __('admin.orders.show.table.unit_price') }}</th>
                            <th class="text-start py-3 font-semibold">{{ __('admin.orders.show.table.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->orderItems as $item)
                            @php $product = $item->product; @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-4">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ optional($product)->primaryImageUrl() ?? asset(\App\Models\Product::FALLBACK_IMAGE) }}"
                                            alt="{{ optional($product)->name ?? __('admin.orders.labels.product') }}"
                                            class="w-14 h-14 rounded-lg object-cover border border-gray-200"
                                            onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">
                                        <div>
                                            <p class="font-semibold text-primary-dark">{{ optional($product)->name ?? __('admin.orders.labels.product') }}</p>
                                            <p class="text-xs text-primary-dark/60 mt-1">{{ __('admin.orders.show.fields.sku') }}: {{ optional($product)->sku ?? __('admin.orders.labels.not_available') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 font-medium text-primary-dark">{{ $item->quantity }}</td>
                                <td class="py-4 font-medium text-primary-dark">€{{ number_format($item->price, 2) }}</td>
                                <td class="py-4 font-semibold text-primary-dark">€{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 md:hidden">
                @foreach($order->orderItems as $item)
                    @php $product = $item->product; @endphp
                    <article class="rounded-xl border border-gray-200 bg-gray-50/70 p-4">
                        <div class="flex gap-3">
                            <img
                                src="{{ optional($product)->primaryImageUrl() ?? asset(\App\Models\Product::FALLBACK_IMAGE) }}"
                                alt="{{ optional($product)->name ?? __('admin.orders.labels.product') }}"
                                class="w-16 h-16 rounded-lg object-cover border border-gray-200"
                                onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-primary-dark truncate">{{ optional($product)->name ?? __('admin.orders.labels.product') }}</p>
                                <p class="text-xs text-primary-dark/60 mt-1">{{ __('admin.orders.show.fields.sku') }}: {{ optional($product)->sku ?? __('admin.orders.labels.not_available') }}</p>
                                <div class="grid grid-cols-2 gap-3 mt-3 text-sm">
                                    <div>
                                        <p class="text-primary-dark/60">{{ __('admin.orders.show.table.qty') }}</p>
                                        <p class="font-medium text-primary-dark">{{ $item->quantity }}</p>
                                    </div>
                                    <div>
                                        <p class="text-primary-dark/60">{{ __('admin.orders.show.table.unit_price') }}</p>
                                        <p class="font-medium text-primary-dark">€{{ number_format($item->price, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-200 flex items-center justify-between text-sm">
                            <span class="text-primary-dark/60">{{ __('admin.orders.show.fields.line_subtotal') }}</span>
                            <span class="font-semibold text-primary-dark">€{{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 h-fit">
            <h2 class="text-lg font-semibold text-primary-dark mb-4">{{ __('admin.orders.show.sections.totals') }}</h2>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-primary-dark/60">{{ __('admin.orders.show.fields.subtotal') }}</span>
                    <span class="font-medium text-primary-dark">€{{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-primary-dark/60">{{ __('admin.orders.show.fields.shipping') }}</span>
                    <span class="font-medium text-primary-dark">€{{ number_format($shipping, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-primary-dark/60">{{ __('admin.orders.show.fields.discount') }}</span>
                    <span class="font-medium text-primary-dark">-€{{ number_format($discount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-primary-dark/60">{{ __('admin.orders.show.fields.tax') }}</span>
                    <span class="font-medium text-primary-dark">€{{ number_format($tax, 2) }}</span>
                </div>
                <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                    <span class="text-base font-semibold text-primary-dark">{{ __('admin.orders.show.fields.grand_total') }}</span>
                    <span class="text-xl font-bold text-primary-dark">€{{ number_format($grandTotal, 2) }}</span>
                </div>
            </div>
        </aside>
    </div>

    @if($order->notes)
        <section class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8">
            <h2 class="text-lg font-semibold text-primary-dark mb-2">{{ __('admin.orders.show.sections.notes') }}</h2>
            <p class="text-primary-dark/80">{{ $order->notes }}</p>
        </section>
    @endif
</div>
@endsection
