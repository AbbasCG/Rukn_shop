<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
        <!-- Back Link -->
            <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm text-primary-dark hover:text-primary-dark/70 font-semibold transition mb-4">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
            Back to My Orders
        </a>

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-4xl md:text-5xl font-bold text-primary-dark" style="font-family: 'Signika', sans-serif;">Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-neutral-600 mt-2">{{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
            </div>

            <!-- Status Badge -->
            <div>
                @php
                    $statusColors = [
                        'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'paid' => 'bg-blue-50 text-blue-800 border-blue-200',
                        'processing' => 'bg-blue-50 text-blue-800 border-blue-200',
                        'shipped' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        'delivered' => 'bg-green-100 text-green-800 border-green-300',
                        'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
                        'refunded' => 'bg-rose-50 text-rose-800 border-rose-200',
                    ];
                    $statusClass = $statusColors[$order->status] ?? 'bg-gray-50 text-gray-800 border-gray-200';
                @endphp
                <span class="inline-block px-6 py-2 rounded-full text-lg font-bold border {{ $statusClass }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 space-y-6 border border-primary-gray/60">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Order Summary Section -->
                <div>
                    <h2 class="text-lg font-bold text-primary-dark mb-4" style="font-family: 'Signika', sans-serif;">Order Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Subtotal:</span>
                            <span class="font-semibold text-primary-dark">€{{ number_format($order->subtotal, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Shipping:</span>
                            <span class="font-semibold text-primary-dark">€{{ number_format($order->shipping_cost, 2, ',', '.') }}</span>
                        </div>
                        <div class="pt-3 border-t border-neutral-200 flex justify-between text-lg">
                            <span class="font-bold text-primary-dark">Total:</span>
                            <span class="font-bold text-primary-dark">€{{ number_format($order->total, 2, ',', '.') }}</span>
                        </div>

                        <div class="pt-4 space-y-2 border-t border-neutral-200">
                            @if($order->payment_method)
                                <div class="text-sm">
                                    <span class="text-neutral-600">Payment Method:</span>
                                    <span class="font-semibold text-primary-dark block">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                                </div>
                            @endif
                            <div class="text-sm">
                                <span class="text-neutral-600">Payment Status:</span>
                                <x-payment-status class="mt-1" :status="$order->payment_status" :order-status="$order->status" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Information Section -->
                <div>
                    <h2 class="text-lg font-bold text-primary-dark mb-4" style="font-family: 'Signika', sans-serif;">Shipping Information</h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-neutral-600">Name:</span>
                            <span class="font-semibold text-primary-dark block">{{ $order->name }}</span>
                        </div>
                        <div>
                            <span class="text-neutral-600">Address:</span>
                            <span class="font-semibold text-primary-dark block">
                                {{ $order->address_line1 }}
                                @if($order->address_line2)
                                    <br>{{ $order->address_line2 }}
                                @endif
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-neutral-600">Postal Code:</span>
                                <span class="font-semibold text-primary-dark block">{{ $order->postal_code }}</span>
                            </div>
                            <div>
                                <span class="text-neutral-600">City:</span>
                                <span class="font-semibold text-primary-dark block">{{ $order->city }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-neutral-600">Country:</span>
                            <span class="font-semibold text-primary-dark block">{{ $order->country }}</span>
                        </div>
                        @if($order->phone)
                            <div>
                                <span class="text-neutral-600">Phone:</span>
                                <span class="font-semibold text-primary-dark block">{{ $order->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-gray/60">
            <h2 class="text-lg font-bold text-primary-dark mb-6" style="font-family: 'Signika', sans-serif;">Order Items</h2>

            <div class="space-y-4">
                @forelse($order->items as $item)
                    <div class="flex gap-4 pb-4 border-b border-neutral-200 last:border-b-0">
                        <!-- Product Image -->
                        <div class="flex-shrink-0">
                            <img
                                src="{{ $item->product->images[0] ?? asset('images/placeholder.jpg') }}"
                                alt="{{ $item->product->name }}"
                                class="w-20 h-20 rounded-lg object-cover border border-neutral-200"
                            >
                        </div>

                        <!-- Product Details -->
                        <div class="flex-1">
                            <a
                                href="{{ route('products.show', $item->product) }}"
                                class="text-lg font-semibold text-primary-dark hover:text-primary-dark/70 transition"
                            >
                                {{ $item->product->name }}
                            </a>
                            <p class="text-sm text-neutral-600 mt-1">SKU: {{ $item->product->sku ?? 'N/A' }}</p>

                            <!-- Quantity and Price -->
                            <div class="flex gap-6 mt-3 text-sm">
                                <div>
                                    <span class="text-neutral-600">Quantity:</span>
                                    <span class="font-semibold text-primary-dark ml-1">{{ $item->quantity }}x</span>
                                </div>
                                <div>
                                    <span class="text-neutral-600">Price per unit:</span>
                                    <span class="font-semibold text-primary-dark ml-1">€{{ number_format($item->price, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Item Total -->
                        <div class="flex-shrink-0 text-right">
                            <p class="text-sm text-neutral-600">Item Total</p>
                            <p class="text-2xl font-bold text-primary-dark">€{{ number_format($item->quantity * $item->price, 2, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-neutral-600 py-8">No items found in this order.</p>
                @endforelse
            </div>
        </div>

        <!-- Order Timeline (Enhanced Status Steps) -->
        <div class="bg-gradient-to-br from-primary-gray/40 via-white to-white rounded-2xl shadow-sm p-6 border border-primary-gray/70">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-primary-dark" style="font-family: 'Signika', sans-serif;">Order Status</h2>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Live tracking</span>
            </div>

            @php
                $steps = ['pending', 'paid', 'processing', 'shipped', 'delivered'];
                $currentIndex = array_search($order->status, $steps);
                if ($currentIndex === false) $currentIndex = 0;
                $progressPercent = ($currentIndex) / (count($steps) - 1) * 100;
            @endphp

            <!-- Progress track -->
            <div class="relative mb-8" style="--progress: {{ $progressPercent }}%;">
                <div class="absolute inset-0 h-2 rounded-full bg-neutral-200"></div>
                <div class="absolute inset-0 h-2 rounded-full bg-gradient-to-r from-emerald-500 to-emerald-400" style="width: var(--progress);"></div>
                <div class="relative flex justify-between">
                    @foreach($steps as $index => $step)
                        @php
                            $isDone = $index <= $currentIndex;
                            $isCurrent = $index === $currentIndex;
                        @endphp
                        <div class="flex flex-col items-center text-center w-24 -ml-3 first:ml-0 last:ml-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 {{ $isDone ? 'bg-white border-emerald-500 text-emerald-700 shadow-sm' : 'bg-white border-neutral-300 text-neutral-500' }}">
                                @if($isDone && !$isCurrent)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @elseif($isCurrent)
                                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                                @else
                                    <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <div class="mt-2 text-xs font-semibold {{ $isDone ? 'text-emerald-800' : 'text-neutral-600' }} uppercase tracking-wide">
                                {{ ucfirst($step) }}
                            </div>
                            @if($isCurrent)
                                <span class="mt-1 text-[11px] text-emerald-700 font-medium">In progress</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Status hint -->
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800" role="status">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <div>
                    <p class="text-sm font-semibold">Current status: {{ ucfirst($order->status) }}</p>
                    <p class="text-xs text-emerald-700">We'll update you as your order moves to the next step.</p>
                </div>
            </div>
        </div>

        <!-- Notes Section (if present) -->
        @if($order->notes)
            <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                <h2 class="text-lg font-bold text-blue-900 mb-2" style="font-family: 'Signika', sans-serif;">Order Notes</h2>
                <p class="text-blue-800">{{ $order->notes }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
