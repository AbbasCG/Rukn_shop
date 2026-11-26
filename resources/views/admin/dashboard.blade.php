@extends('layouts.admin', ['title' => __('admin.dashboard.page_title')])

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <!-- Welcome Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-primary-dark border-s-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-primary-dark">{{ __('admin.dashboard.title', ['name' => auth()->user()->name]) }}</h1>
                <p class="text-sm text-primary-dark/60 mt-1">{{ \Carbon\Carbon::now()->translatedFormat('l, F j, Y') }}</p>
            </div>
            <div class="hidden md:block">
                <svg class="w-16 h-16 text-primary-dark/10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Products Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.dashboard.cards.total_products') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ \App\Models\Product::count() }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.dashboard.cards.in_inventory') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Categories Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.dashboard.cards.total_categories') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ \App\Models\Category::count() }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.dashboard.cards.active_categories') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Orders Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.dashboard.cards.total_orders') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ \App\Models\Order::count() }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.dashboard.cards.all_time') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Revenue Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.dashboard.cards.total_revenue') }}</p>
                    @php
                    $completedOrderIds = \App\Models\Order::where('payment_status', 'paid')->pluck('id');
                    $revenue = \App\Models\order_items::whereIn('order_id', $completedOrderIds)->sum(\DB::raw('price * quantity'));
                    @endphp
                    <p class="text-3xl font-bold text-primary-dark mt-2">€{{ number_format($revenue, 2) }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.dashboard.cards.from_paid_orders') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg
                        class="w-8 h-8 text-primary-dark"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <!-- Euro sign -->
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15.5 7.5c-.8-.6-1.8-1-3.5-1-3 0-5.5 2.7-5.5 5.5S9 17.5 12 17.5c1.7 0 2.7-.4 3.5-1M6.5 10.5H13M6.5 13.5H13" />
                        <!-- Circle -->
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                            stroke-width="2" />
                    </svg>


                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Sales Chart (2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-primary-dark">{{ __('admin.dashboard.sales.title') }}</h2>
                <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.dashboard.sales.subtitle') }}</p>
            </div>
            <div class="bg-primary-dark/5 rounded-lg p-8 flex items-center justify-center" style="height: 300px;">
                <div class="text-center">
                    <canvas id="salesChart" width="600" height="250"></canvas>
                    <p class="text-sm text-primary-dark/60 mt-4">{{ __('admin.dashboard.sales.chart_placeholder') }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-primary-dark">{{ __('admin.dashboard.quick_actions.title') }}</h2>
                <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.dashboard.quick_actions.subtitle') }}</p>
            </div>
            <div class="space-y-3">
                <a href="{{ r('admin.products.create') }}" class="flex items-center justify-between w-full px-4 py-3 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    <span>{{ __('admin.dashboard.quick_actions.add_new_product') }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </a>
                <a href="{{ r('admin.categories.create') }}" class="flex items-center justify-between w-full px-4 py-3 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    <span>{{ __('admin.dashboard.quick_actions.add_new_category') }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </a>
                <a href="{{ r('admin.users.create') }}" class="flex items-center justify-between w-full px-4 py-3 bg-white border border-primary-dark text-primary-dark font-semibold rounded-lg hover:bg-primary-dark/5 transition-all duration-300">
                    <span>{{ __('admin.dashboard.quick_actions.add_new_user') }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </a>
                <a href="{{ r('admin.orders.index') }}" class="flex items-center justify-between w-full px-4 py-3 bg-white border border-primary-dark text-primary-dark font-semibold rounded-lg hover:bg-primary-dark/5 transition-all duration-300">
                    <span>{{ __('admin.dashboard.quick_actions.view_all_orders') }}</span>
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Low Stock Alerts -->
            <div class="mt-8">
                <h3 class="text-lg font-bold text-primary-dark mb-4 flex items-center">
                    <svg class="w-5 h-5 me-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ __('admin.dashboard.low_stock.title') }}
                </h3>
                @php
                $lowStockProducts = \App\Models\Product::where('stock', '<', 5)->orderBy('stock', 'asc')->limit(5)->get();
                    @endphp
                    @if($lowStockProducts->count() > 0)
                    <div class="space-y-2">
                        @foreach($lowStockProducts as $product)
                        <div class="flex items-center justify-between p-3 rounded-lg {{ $product->stock <= 1 ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200' }}">
                            <div class="flex-1">
                                <p class="text-sm font-medium {{ $product->stock <= 1 ? 'text-red-900' : 'text-yellow-900' }}">{{ Str::limit($product->name, 25) }}</p>
                            </div>
                            <span class="ms-2 px-2 py-1 text-xs font-bold rounded {{ $product->stock <= 1 ? 'bg-red-600 text-white' : 'bg-yellow-600 text-white' }}">
                                {{ __('admin.dashboard.low_stock.items_left', ['count' => $product->stock]) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-primary-dark/60 bg-green-50 p-3 rounded-lg border border-green-200">
                        {{ __('admin.dashboard.low_stock.all_well_stocked') }}
                    </p>
                    @endif
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-primary-dark">{{ __('admin.dashboard.recent_orders.title') }}</h2>
            <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.dashboard.recent_orders.subtitle') }}</p>
        </div>
        @php
        $recentOrders = \App\Models\Order::with(['user', 'orderItems'])->latest()->limit(5)->get();
        @endphp
        @if($recentOrders->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-primary-gray">
                        <th class="text-start py-3 px-4 text-sm font-semibold text-primary-dark">{{ __('admin.dashboard.recent_orders.table.order_id') }}</th>
                        <th class="text-start py-3 px-4 text-sm font-semibold text-primary-dark">{{ __('admin.dashboard.recent_orders.table.customer') }}</th>
                        <th class="text-start py-3 px-4 text-sm font-semibold text-primary-dark">{{ __('admin.dashboard.recent_orders.table.total') }}</th>
                        <th class="text-start py-3 px-4 text-sm font-semibold text-primary-dark">{{ __('admin.dashboard.recent_orders.table.status') }}</th>
                        <th class="text-start py-3 px-4 text-sm font-semibold text-primary-dark">{{ __('admin.dashboard.recent_orders.table.date') }}</th>
                        <th class="text-start py-3 px-4 text-sm font-semibold text-primary-dark">{{ __('admin.dashboard.recent_orders.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr class="border-b border-primary-gray/30 hover:bg-primary-dark/5 transition-colors duration-200">
                        <td class="py-3 px-4">
                            <span class="text-sm font-medium text-primary-dark">#{{ $order->id }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-sm text-primary-dark">{{ $order->user->name ?? __('admin.dashboard.recent_orders.guest') }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @php
                            $orderTotal = $order->orderItems->sum(function($item) {
                            return $item->price * $item->quantity;
                            });
                            @endphp
                            <span class="text-sm font-semibold text-primary-dark">${{ number_format($orderTotal, 2) }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @php
                            $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'paid' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'completed' => 'bg-green-100 text-green-800 border-green-200',
                            'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                            ];
                            $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            $statusLabel = __('admin.dashboard.statuses.' . $order->status);
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $colorClass }}">
                                {{ $statusLabel !== 'admin.dashboard.statuses.' . $order->status ? $statusLabel : ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-sm text-primary-dark/70">{{ $order->created_at->translatedFormat('M d, Y') }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="{{ r('admin.orders.show', $order) }}" class="text-sm font-medium text-primary-dark hover:text-primary-dark/70 transition-colors duration-200">
                                {{ __('admin.dashboard.recent_orders.view_action') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-primary-dark/20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="text-primary-dark/60">{{ __('admin.dashboard.recent_orders.empty') }}</p>
        </div>
        @endif
    </div>

</div>
@endsection
