<x-app-layout :title="__('wishlist.title')" :noindex="true">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 min-h-screen">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary-dark">{{ __('wishlist.title') }}</h1>
            <p class="mt-2 text-sm text-neutral-600">
                @guest
                    {{ __('wishlist.guest_note') }}
                    <a href="{{ r('login') }}" class="font-semibold text-primary-dark underline underline-offset-2">{{ __('wishlist.login') }}</a>
                @else
                    {{ __('wishlist.subtitle') }}
                @endguest
            </p>
        </div>

        @if ($products->isEmpty())
            <div class="rounded-3xl border border-primary-gray/60 bg-white p-12 text-center">
                <p class="text-lg font-semibold text-primary-dark">{{ __('wishlist.empty') }}</p>
                <p class="mt-2 text-sm text-neutral-600">{{ __('wishlist.empty_hint') }}</p>
                <a href="{{ r('products.index') }}" class="btn-primary mt-6">{{ __('cart.actions.continue') }}</a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
