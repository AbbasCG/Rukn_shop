<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary-dark leading-tight">
            {{ __('Shopping Cart') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($cartItems->count() > 0)
                <div class="bg-primary-light overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($cartItems as $item)
                                <div class="flex items-center justify-between border-b border-primary-gray pb-4">
                                    <div class="flex items-center space-x-4">
                                        @if($item->product->image_url)
                                            <img src="{{ asset('storage/' . $item->product->image_url) }}" 
                                                 alt="{{ $item->product->name }}" 
                                                 class="w-20 h-20 object-cover rounded">
                                        @endif
                                        <div>
                                            <h3 class="text-lg font-medium text-primary-dark">{{ $item->product->name }}</h3>
                                            <p class="text-sm text-primary-dark">${{ number_format($item->product->price, 2) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-primary-dark">Quantity: {{ $item->quantity }}</span>
                                        </div>
                                        <div class="text-lg font-semibold text-primary-dark">
                                            ${{ number_format($item->product->price * $item->quantity, 2) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-primary-gray">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-semibold text-primary-dark">Total:</span>
                                <span class="text-xl font-semibold text-primary-dark">
                                    ${{ number_format($cartItems->sum(function($item) {
                                        return $item->product->price * $item->quantity;
                                    }), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-primary-light overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-primary-dark text-lg">{{ __('Your cart is empty.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
