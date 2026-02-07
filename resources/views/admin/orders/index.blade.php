@extends('layouts.admin', ['title' => 'Orders'])

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">
    
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">Orders</h1>
            <p class="text-sm text-primary-dark/60 mt-1">Manage customer orders and track fulfillment</p>
        </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <form method="GET" class="bg-white rounded-xl shadow-lg p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search Input -->
            <div>
                <label for="search" class="block text-sm font-medium text-primary-dark mb-2">Search</label>
                <input 
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Order #, customer name..."
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-primary-dark mb-2">Status</label>
                <select 
                    id="status"
                    name="status"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">All Orders</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="processing" @selected(request('status') === 'processing')>Processing</option>
                    <option value="shipped" @selected(request('status') === 'shipped')>Shipped</option>
                    <option value="delivered" @selected(request('status') === 'delivered')>Delivered</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <label for="payment_status" class="block text-sm font-medium text-primary-dark mb-2">Payment</label>
                <select 
                    id="payment_status"
                    name="payment_status"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">All</option>
                    <option value="open" @selected(request('payment_status') === 'open')>Open</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                    <option value="failed" @selected(request('payment_status') === 'failed')>Failed</option>
                    <option value="refunded" @selected(request('payment_status') === 'refunded')>Refunded</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button 
                    type="submit"
                    class="flex-1 px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    Search
                </button>
                <a 
                    href="{{ route('admin.orders.index') }}"
                    class="px-4 py-2 bg-white border border-primary-gray text-primary-dark font-medium rounded-lg hover:bg-primary-dark/5 transition-all duration-300">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Orders Table Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Table -->
        @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-primary-gray bg-primary-dark/5">
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Order #</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Customer</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Total</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Status</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Payment</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Date</th>
                            <th class="text-right py-4 px-6 text-sm font-semibold text-primary-dark">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr class="border-b border-primary-gray/30 hover:bg-primary-dark/5 transition-colors duration-200">
                                <td class="py-4 px-6">
                                    <span class="text-sm font-semibold text-primary-dark">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="text-sm font-medium text-primary-dark">{{ $order->user->name ?? 'Guest' }}</p>
                                        <p class="text-xs text-primary-dark/60">{{ $order->user->email ?? 'N/A' }}</p>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    @php
                                        $orderTotal = $order->orderItems->sum(function($item) {
                                            return $item->price * $item->quantity;
                                        });
                                    @endphp
                                    <span class="text-sm font-semibold text-primary-dark">€{{ number_format($orderTotal, 2) }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'delivered' => 'bg-green-100 text-green-800 border-green-200',
                                            'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                                        ];
                                        $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $colorClass }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    @php
                                        $paymentColors = [
                                            'open' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'paid' => 'bg-green-100 text-green-800 border-green-200',
                                            'failed' => 'bg-red-100 text-red-800 border-red-200',
                                            'refunded' => 'bg-gray-100 text-gray-800 border-gray-200',
                                        ];
                                        $paymentClass = $paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $paymentClass }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm text-primary-dark/70">{{ $order->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <a 
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-dark text-white hover:bg-primary-dark/90 transition-all duration-300">
                                        View →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-primary-dark/5 px-6 py-4 border-t border-primary-gray">
                {{ $orders->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-16 px-6">
                <svg class="w-16 h-16 text-primary-dark/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <p class="text-lg font-medium text-primary-dark mb-2">No orders found</p>
                <p class="text-sm text-primary-dark/60 text-center mb-6">Try adjusting your search or filter criteria</p>
                <a 
                    href="{{ route('admin.orders.index') }}"
                    class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    Clear Filters
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
