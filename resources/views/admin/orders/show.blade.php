@extends('layouts.admin', ['title' => 'Order #'.$order->id])

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <!-- Header Section -->
    <div>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-dark">Order #{{ $order->id }}</h1>
                <p class="text-sm text-primary-dark/60 mt-1">View and manage order details</p>
            </div>
            
            <!-- Quick Status Update -->
            <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="flex items-center gap-3 bg-white rounded-xl shadow-lg p-4">
                @csrf
                @method('PATCH')
                <label for="status" class="text-sm font-medium text-primary-dark">Status:</label>
                <select 
                    id="status"
                    name="status" 
                    class="px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button 
                    type="submit"
                    class="px-6 py-2.5 bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-dark/90 transition-all duration-300">
                    Update
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Customer & Items -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Information Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-primary-dark mb-4 pb-3 border-b border-primary-gray">Customer Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-primary-dark/60">Name</p>
                        <p class="text-base font-medium text-primary-dark">{{ $order->user->name ?? 'Guest' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-primary-dark/60">Email</p>
                        <p class="text-base font-medium text-primary-dark">{{ $order->user->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-primary-dark/60">Shipping Address</p>
                        <p class="text-base font-medium text-primary-dark">{{ $order->shipping_address ?? 'Not specified' }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Items Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-primary-dark mb-4 pb-3 border-b border-primary-gray">Order Items</h2>
                <div class="divide-y divide-primary-gray">
                    @foreach($order->orderItems as $item)
                        <div class="py-4 flex items-center justify-between first:pt-0">
                            <div class="flex-1">
                                <p class="font-medium text-primary-dark">{{ $item->product->name ?? 'Product' }}</p>
                                <p class="text-sm text-primary-dark/60 mt-1">Quantity: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-primary-dark/60">Price per item</p>
                                <p class="font-semibold text-primary-dark">€{{ number_format($item->price, 2) }}</p>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-sm text-primary-dark/60">Subtotal</p>
                                <p class="font-semibold text-primary-dark">€{{ number_format($item->price * $item->quantity, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Summary & Details -->
        <div class="space-y-6">
            <!-- Order Summary Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-primary-dark mb-4 pb-3 border-b border-primary-gray">Order Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-primary-dark/60">Subtotal</span>
                        <span class="font-semibold text-primary-dark">€{{ number_format($order->orderItems->sum(fn($item) => $item->price * $item->quantity), 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-primary-dark/60">Shipping</span>
                        <span class="font-semibold text-primary-dark">€0.00</span>
                    </div>
                    <div class="pt-3 border-t border-primary-gray flex justify-between items-center">
                        <span class="text-base font-semibold text-primary-dark">Total</span>
                        <span class="text-lg font-bold text-primary-dark">€{{ number_format($order->orderItems->sum(fn($item) => $item->price * $item->quantity), 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Additional Details Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-primary-dark mb-4 pb-3 border-b border-primary-gray">Details</h2>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-primary-dark/60 mb-1">Status</p>
                        <span class="inline-block px-3 py-1 rounded-full font-medium 
                            @if($order->status === 'pending') bg-amber-100 text-amber-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'shipped') bg-indigo-100 text-indigo-800
                            @elseif($order->status === 'delivered') bg-emerald-100 text-emerald-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-primary-dark/60 mb-1">Payment Method</p>
                        <p class="font-medium text-primary-dark">{{ $order->payment_method ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-primary-dark/60 mb-1">Order Date</p>
                        <p class="font-medium text-primary-dark">{{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-primary-dark/60 mb-1">Notes</p>
                        <p class="font-medium text-primary-dark">{{ $order->notes ?? 'No notes' }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <a 
                    href="{{ route('admin.orders.index') }}"
                    class="flex-1 px-6 py-2.5 border border-primary-gray text-primary-dark rounded-lg font-semibold hover:bg-primary-gray transition-all duration-300 text-center">
                    Back
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
