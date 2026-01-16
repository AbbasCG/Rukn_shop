<x-app-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <div class="min-h-screen flex flex-col max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8" style="font-family: 'Signika', sans-serif;" dir="{{ $dir }}">
        <!-- Page Header -->
        <div class="space-y-2 text-center sm:text-left">
            <h1 class="text-4xl md:text-5xl font-bold text-primary-dark tracking-tight">{{ __('orders.title') }}</h1>
            <p class="text-base md:text-lg text-neutral-600">{{ __('orders.subtitle') }}</p>
        </div>

        @if($orders->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-gray/60 p-12 text-center">
            <div class="w-24 h-24 mx-auto mb-6 bg-primary-gray/40 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-primary-dark/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9a2 2 0 012-2z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-primary-dark mb-2">{{ __('orders.empty.title') }}</h2>
            <p class="text-neutral-600 mb-8">{{ __('orders.empty.subtitle') }}</p>
            <a
                href="{{ route('products.index') }}"
                class="inline-flex items-center px-8 py-3 bg-primary-dark text-white text-sm font-semibold rounded-xl hover:bg-primary-dark/90 transition transform hover:-translate-y-0.5 shadow-md hover:shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                </svg>
                {{ __('orders.empty.button') }}
            </a>
        </div>
        @else
        <!-- Orders List -->
        <div class="space-y-4 flex-1">
            @foreach($orders as $order)
            <div class="bg-white rounded-2xl shadow-sm border border-primary-gray/60 p-4 md:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 hover:shadow-md transition">
                <div class="flex-1 space-y-2">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-primary-dark">{{ __('orders.table.order_number') }} #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h3>
                            <p class="text-sm text-neutral-600">{{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-neutral-700"><span class="font-medium">{{ __('orders.table.total') }}:</span> €{{ number_format($order->total, 2, ',', '.') }}</p>
                </div>

                <div class="flex items-center gap-4">
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
                        <span class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold border {{ $statusClass }}">
                            {{ __('orders.status.' . $order->status) }}
                        </span>
                    </div>

                    <!-- View Details Button -->
                    <a
                        href="{{ route('orders.show', $order) }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-neutral-300 text-neutral-800 hover:bg-neutral-100 transition whitespace-nowrap">
                        {{ __('orders.actions.view_details') }}
                        <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $orders->links() }}
        </div>

        <!-- Back to Account -->
        <div class="text-center pt-8 border-t border-neutral-200 pb-4">
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center text-primary-dark hover:text-primary-dark/70 font-semibold transition">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                {{ __('orders.actions.back_to_profile') }}
            </a>
        </div>
        @endif
    </div>
</x-app-layout>