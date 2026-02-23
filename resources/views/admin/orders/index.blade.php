@extends('layouts.admin', ['title' => __('admin.orders.index.title')])

@section('content')
@php
    $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
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
@endphp

<div class="w-full max-w-7xl mx-auto space-y-6" dir="{{ $dir }}">
    <section class="bg-white rounded-xl shadow-lg p-6 space-y-4">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-primary-dark">{{ __('admin.orders.index.title') }}</h1>
                <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.orders.index.subtitle') }}</p>
            </div>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium bg-gray-100 text-gray-700 w-fit">
                {{ __('admin.orders.index.total_count', ['count' => $orders->total()]) }}
            </span>
        </div>

        <form method="GET" class="mt-2 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="q" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.orders.index.filters.search') }}</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ __('admin.orders.index.filters.search_placeholder') }}"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.orders.index.filters.status') }}</label>
                <select
                    id="status"
                    name="status"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">{{ __('admin.orders.index.filters.all_statuses') }}</option>
                    <option value="pending" @selected(request('status') === 'pending')>{{ __('admin.orders.status.pending') }}</option>
                    <option value="processing" @selected(request('status') === 'processing')>{{ __('admin.orders.status.processing') }}</option>
                    <option value="shipped" @selected(request('status') === 'shipped')>{{ __('admin.orders.status.shipped') }}</option>
                    <option value="delivered" @selected(request('status') === 'delivered')>{{ __('admin.orders.status.delivered') }}</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('admin.orders.status.cancelled') }}</option>
                </select>
            </div>

            <div>
                <label for="from" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.orders.index.filters.from') }}</label>
                <input
                    type="date"
                    id="from"
                    name="from"
                    value="{{ request('from') }}"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>

            <div>
                <label for="to" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.orders.index.filters.to') }}</label>
                <input
                    type="date"
                    id="to"
                    name="to"
                    value="{{ request('to') }}"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    {{ __('admin.orders.actions.search') }}
                </button>
                <a href="{{ r('admin.orders.index') }}" class="px-4 py-2 bg-white border border-primary-gray text-primary-dark font-medium rounded-lg hover:bg-primary-dark/5 transition-all duration-300">
                    {{ __('admin.orders.actions.reset') }}
                </a>
            </div>
        </form>
    </section>

    <section class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        @if($orders->count() > 0)
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-start py-4 px-6 font-semibold text-primary-dark">{{ __('admin.orders.table.order_number') }}</th>
                            <th class="text-start py-4 px-6 font-semibold text-primary-dark">{{ __('admin.orders.table.customer') }}</th>
                            <th class="text-start py-4 px-6 font-semibold text-primary-dark">{{ __('admin.orders.table.date') }}</th>
                            <th class="text-start py-4 px-6 font-semibold text-primary-dark">{{ __('admin.orders.table.status') }}</th>
                            <th class="text-start py-4 px-6 font-semibold text-primary-dark">{{ __('admin.orders.table.total') }}</th>
                            <th class="text-start py-4 px-6 font-semibold text-primary-dark">{{ __('admin.orders.table.payment') }}</th>
                            <th class="text-start py-4 px-6 font-semibold text-primary-dark w-[320px]">{{ __('admin.orders.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($orders as $order)
                            @php
                                $lineSubtotal = $order->orderItems->sum(fn($item) => $item->price * $item->quantity);
                                $statusClass = $statusColors[strtolower((string) $order->status)] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-4 px-6 font-semibold text-primary-dark">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-4 px-6">
                                    <p class="font-medium text-primary-dark">{{ $order->user->name ?? __('admin.orders.labels.guest') }}</p>
                                    <p class="text-xs text-primary-dark/60">{{ $order->user->email ?? __('admin.orders.labels.not_available') }}</p>
                                </td>
                                <td class="py-4 px-6 text-primary-dark/70">{{ $order->created_at->translatedFormat('M d, Y') }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                                        {{ $statusLabel(strtolower((string) $order->status)) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-semibold text-primary-dark">€{{ number_format($lineSubtotal, 2) }}</td>
                                <td class="py-4 px-6 text-primary-dark/80">{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : '-' }}</td>
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-center gap-2 flex-nowrap min-w-0">
                                        <a href="{{ r('admin.orders.show', $order) }}" class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl bg-primary-dark px-3 text-xs font-semibold leading-none text-white hover:bg-primary-dark/90 transition">
                                            {{ __('admin.orders.actions.view') }}
                                        </a>
                                        <form action="{{ r('admin.orders.update', $order) }}" method="POST" class="flex items-center gap-2 flex-nowrap min-w-0">
                                            @csrf
                                            @method('PATCH')
                                            <div class="relative min-w-[150px] max-w-[190px]">
                                                <select
                                                    name="status"
                                                    class="block h-9 w-full appearance-none truncate rounded-xl border border-gray-200 bg-white ps-3 pe-9 text-xs font-medium text-primary-dark focus:border-primary-dark/30 focus:outline-none focus:ring-2 focus:ring-primary-dark/10 hover:border-gray-300">
                                                    @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                                                        <option value="{{ $status }}" @selected($order->status === $status)>{{ $statusLabel($status) }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-primary-dark/50">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <button type="submit" class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold leading-none text-primary-dark hover:bg-gray-50 focus:border-primary-dark/30 focus:outline-none focus:ring-2 focus:ring-primary-dark/10 transition">
                                                {{ __('admin.orders.actions.mark_as') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="lg:hidden p-4 space-y-4 bg-gray-50/70">
                @foreach($orders as $order)
                    @php
                        $lineSubtotal = $order->orderItems->sum(fn($item) => $item->price * $item->quantity);
                        $statusClass = $statusColors[strtolower((string) $order->status)] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-primary-dark">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                                <p class="text-xs text-primary-dark/60 mt-1">{{ $order->created_at->translatedFormat('M d, Y') }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabel(strtolower((string) $order->status)) }}</span>
                        </div>

                        <div class="text-sm">
                            <p class="font-medium text-primary-dark">{{ $order->user->name ?? __('admin.orders.labels.guest') }}</p>
                            <p class="text-primary-dark/60">{{ $order->user->email ?? __('admin.orders.labels.not_available') }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-primary-dark/60">{{ __('admin.orders.table.total') }}</p>
                                <p class="font-semibold text-primary-dark">€{{ number_format($lineSubtotal, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-primary-dark/60">{{ __('admin.orders.table.payment') }}</p>
                                <p class="font-medium text-primary-dark">{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : '-' }}</p>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <a href="{{ r('admin.orders.show', $order) }}" class="inline-flex h-9 w-full sm:w-auto items-center justify-center rounded-xl bg-primary-dark px-3 text-xs font-semibold leading-none text-white hover:bg-primary-dark/90 transition">
                                {{ __('admin.orders.actions.view') }}
                                </a>
                                <form action="{{ r('admin.orders.update', $order) }}" method="POST" class="w-full sm:w-auto flex items-center gap-2 min-w-0">
                                    @csrf
                                    @method('PATCH')
                                    <div class="relative min-w-0 flex-1 sm:flex-none sm:min-w-[150px] sm:max-w-[190px]">
                                        <select
                                            name="status"
                                            class="block h-9 w-full appearance-none truncate rounded-xl border border-gray-200 bg-white ps-3 pe-9 text-xs font-medium text-primary-dark focus:border-primary-dark/30 focus:outline-none focus:ring-2 focus:ring-primary-dark/10 hover:border-gray-300">
                                            @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                                                <option value="{{ $status }}" @selected($order->status === $status)>{{ $statusLabel($status) }}</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-primary-dark/50">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <button type="submit" class="inline-flex h-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold leading-none text-primary-dark hover:bg-gray-50 focus:border-primary-dark/30 focus:outline-none focus:ring-2 focus:ring-primary-dark/10 transition">
                                        {{ __('admin.orders.actions.mark_as') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                {{ $orders->links() }}
            </div>
        @else
            <div class="py-16 px-6 text-center">
                <p class="text-lg font-semibold text-primary-dark">{{ __('admin.orders.empty.title') }}</p>
                <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.orders.empty.subtitle') }}</p>
                <a href="{{ r('admin.orders.index') }}" class="mt-5 inline-flex rounded-lg bg-primary-dark px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark/90 transition">
                    {{ __('admin.orders.actions.clear_filters') }}
                </a>
            </div>
        @endif
    </section>
</div>
@endsection
