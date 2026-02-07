<x-app-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <!-- Breadcrumbs -->
    <div class="bg-primary-50 dark:bg-primary-900 border-b border-primary-200 dark:border-primary-700" dir="{{ $dir }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex text-sm text-neutral-500 dark:text-primary-300">
                <a href="{{ route('home') }}" class="hover:text-primary-800 transition-colors font-medium">{{ __('product.breadcrumb_home') }}</a>
                <span class="mx-2">/</span>
                <a href="{{ route('products.index') }}" class="hover:text-primary-800 transition-colors font-medium">{{ __('product.breadcrumb_products') }}</a>
                @if($product->category)
                    <span class="mx-2">/</span>
                    <span class="hover:text-primary-800 transition-colors font-medium">{{ $product->category->name }}</span>
                @endif
                <span class="mx-2">/</span>
                <span class="text-primary-800 dark:text-primary-50 font-bold">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <!-- Main Product Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12" dir="{{ $dir }}">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
            <!-- Left Column - Image Gallery -->
            <div class="space-y-4 h-full">
                <!-- Main Image -->
                <div class="w-full bg-primary-100 dark:bg-primary-100 rounded-2xl overflow-hidden shadow-xl border border-neutral-200 dark:border-neutral-300" style="aspect-ratio: 4/5;">
                    <img 
                        id="mainProductImage" 
                        src="{{ $product->primaryImage() ? asset('storage/' . $product->primaryImage()->path) : asset('storage/images/placeholder.jpg') }}" 
                        alt="{{ $product->name }}" 
                        class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                    >
                </div>

                <!-- Thumbnail Gallery -->
                @if($product->images && $product->images->count() > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($product->images as $index => $image)
                            <button 
                                data-image="{{ asset('storage/' . $image->path) }}"
                                onclick="changeMainImage(this.dataset.image)" 
                                class="aspect-square bg-primary-100 dark:bg-primary-100 rounded-xl overflow-hidden border-2 {{ $image->is_primary ? 'border-primary-800' : 'border-neutral-200 dark:border-neutral-300' }} hover:border-primary-800 dark:hover:border-primary-800 transition-all cursor-pointer">
                                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->name }} - Image {{ $index + 1 }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right Column - Product Info -->
            <div class="space-y-5">
                <!-- Category Badge -->
                @if($product->category)
                    <div class="inline-block">
                        <span class="px-3 py-1 bg-primary-100 dark:bg-primary-700 text-primary-800 dark:text-primary-50 text-xs font-bold uppercase tracking-wider rounded-full">{{ $product->category->name }}</span>
                    </div>
                @endif

                <!-- Product Name & Share Button -->
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h1 class="text-4xl lg:text-5xl font-bold text-primary-800 dark:text-primary-800 leading-tight flex-1 min-w-0">{{ $product->name }}</h1>
                    
                    <!-- Share Button (compact variant) -->
                    <div class="flex-shrink-0">
                        <x-product-share :product="$product" variant="compact" />
                    </div>
                </div>

                <!-- Rating -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= floor($product->rating))
                                <svg class="w-6 h-6 text-warning fill-current" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            @elseif($i - 0.5 <= $product->rating)
                                <svg class="w-6 h-6 text-warning" viewBox="0 0 20 20">
                                    <defs>
                                        <linearGradient id="half-{{ $i }}">
                                            <stop offset="50%" stop-color="currentColor"/>
                                            <stop offset="50%" stop-color="#D4D4D4"/>
                                        </linearGradient>
                                    </defs>
                                    <path fill="url(#half-{{ $i }})" d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-neutral-300 dark:text-neutral-600 fill-current" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            @endif
                        @endfor
                    </div>
                    <span class="text-base text-neutral-500 dark:text-primary-300 font-medium">{{ number_format($product->rating, 1) }} <span class="text-neutral-400">({{ __('product.reviews_count', ['count' => $reviews->count()]) }})</span></span>
                </div>

                <!-- Price -->
                <div class="text-5xl font-bold text-primary-800 dark:text-primary-800">€{{ number_format($product->price, 2) }}</div>

                <!-- Availability -->
                @if($product->stock > 0)
                    <div class="flex items-center gap-2 text-success dark:text-success bg-success-light/20 dark:bg-success-dark/20 px-4 py-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-bold">{{ __('product.in_stock_available', ['count' => $product->stock]) }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 text-error dark:text-error bg-error-light/20 dark:bg-error-dark/20 px-4 py-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-bold">{{ __('product.out_of_stock') }}</span>
                    </div>
                @endif

                <!-- Short Description -->
                <p class="text-base text-neutral-600 dark:text-primary-300 leading-relaxed">{{ $product->short_description }}</p>

                <!-- Product Details
                <div class="bg-primary-100 dark:bg-primary-100 rounded-xl p-5 space-y-2.5 border border-primary-200 dark:border-neutral-300">
                    <div class="flex justify-between items-center">
                        <span class="text-neutral-500 dark:text-primary-300 font-medium">SKU:</span>
                        <span class="font-bold text-primary-800 dark:text-primary-800">{{ $product->sku }}</span>
                    </div>
                    @if($product->brand)
                        <div class="flex justify-between items-center">
                            <span class="text-neutral-500 dark:text-primary-300 font-medium">Brand:</span>
                            <span class="font-bold text-primary-800 dark:text-primary-800">{{ $product->brand }}</span>
                        </div>
                    @endif
                    @if($product->category)
                        <div class="flex justify-between items-center">
                            <span class="text-neutral-500 dark:text-primary-300 font-medium">Category:</span>
                            <span class="font-bold text-primary-800 dark:text-primary-800">{{ $product->category->name }}</span>
                        </div>
                    @endif
                </div> -->

                <!-- Options -->
                <div class="space-y-5">
                    <!-- Color Selector -->
                    <!-- <div>
                        <div class="flex items-center gap-4">
                            <label class="cursor-pointer group">
                                <input type="radio" name="color" value="black" checked class="hidden">
                                <div class="w-8 h-8 bg-black rounded-full border-2 border-neutral-300 hover:border-primary-800 transition-all group-has-[:checked]:ring-2 group-has-[:checked]:ring-primary-800 group-has-[:checked]:ring-offset-2"></div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="color" value="white" class="hidden">
                                <div class="w-8 h-8 bg-white border-2 border-neutral-300 rounded-full hover:border-primary-800 transition-all group-has-[:checked]:ring-2 group-has-[:checked]:ring-primary-800 group-has-[:checked]:ring-offset-2"></div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="color" value="navy" class="hidden">
                                <div class="w-8 h-8 bg-blue-900 rounded-full border-2 border-neutral-300 hover:border-primary-800 transition-all group-has-[:checked]:ring-2 group-has-[:checked]:ring-primary-800 group-has-[:checked]:ring-offset-2"></div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="color" value="beige" class="hidden">
                                <div class="w-8 h-8 bg-amber-100 rounded-full border-2 border-neutral-300 hover:border-primary-800 transition-all group-has-[:checked]:ring-2 group-has-[:checked]:ring-primary-800 group-has-[:checked]:ring-offset-2"></div>
                            </label>
                        </div>
                    </div> -->

                    <!-- Size Selector -->
                    <!-- <div>
                        <div class="flex items-center gap-3">
                            <label class="cursor-pointer group">
                                <input type="radio" name="size" value="small" checked class="hidden">
                                <div class="flex items-center justify-center w-10 h-10 border-2 border-neutral-200 dark:border-neutral-300 rounded-lg hover:border-primary-800 transition-all group-has-[:checked]:ring-2 group-has-[:checked]:ring-primary-800 group-has-[:checked]:ring-offset-2">
                                    <span class="text-xs font-bold text-primary-800 dark:text-primary-800">S</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="size" value="medium" class="hidden">
                                <div class="flex items-center justify-center w-10 h-10 border-2 border-neutral-200 dark:border-neutral-300 rounded-lg hover:border-primary-800 transition-all group-has-[:checked]:ring-2 group-has-[:checked]:ring-primary-800 group-has-[:checked]:ring-offset-2">
                                    <span class="text-xs font-bold text-primary-800 dark:text-primary-800">M</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="size" value="large" class="hidden">
                                <div class="flex items-center justify-center w-10 h-10 border-2 border-neutral-200 dark:border-neutral-300 rounded-lg hover:border-primary-800 transition-all group-has-[:checked]:ring-2 group-has-[:checked]:ring-primary-800 group-has-[:checked]:ring-offset-2">
                                    <span class="text-xs font-bold text-primary-800 dark:text-primary-800">L</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="size" value="xlarge" class="hidden">
                                <div class="flex items-center justify-center w-10 h-10 border-2 border-neutral-200 dark:border-neutral-300 rounded-lg hover:border-primary-800 transition-all group-has-[:checked]:ring-2 group-has-[:checked]:ring-primary-800 group-has-[:checked]:ring-offset-2">
                                    <span class="text-xs font-bold text-primary-800 dark:text-primary-800">XL</span>
                                </div>
                            </label>
                        </div>
                    </div> -->
                </div>
                

                <!-- Quantity + Actions -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-start gap-3 pt-4 w-full">
                    <!-- Quantity Selector -->
                    <div class="inline-flex items-center gap-2 flex-shrink-0">
                        <button type="button" onclick="decrementQuantity()" class="w-8 h-8 flex items-center justify-center text-primary-800 dark:text-primary-800 hover:opacity-70 transition-opacity active:scale-90">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <input 
                            type="number" 
                            id="quantity" 
                            name="quantity" 
                            value="1" 
                            min="1" 
                            max="{{ $product->stock }}" 
                            :aria-label="__('product.quantity_label')"
                            class="w-12 h-8 text-center px-2 py-0 bg-transparent text-sm font-bold text-primary-800 dark:text-primary-800 focus:outline-none transition-all [&::-webkit-outer-spin-button]:[appearance:none] [&::-webkit-inner-spin-button]:[appearance:none] [&::-webkit-inner-spin-button]:[margin:0] [&::-webkit-outer-spin-button]:[margin:0]"
                        >
                        <button type="button" onclick="incrementQuantity()" class="w-8 h-8 flex items-center justify-center text-primary-800 dark:text-primary-800 hover:opacity-70 transition-opacity active:scale-90">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Action Buttons Container -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 w-full sm:w-auto">
                        <!-- Add to Cart Button -->
                        <form action="{{ route('cart.store') }}" method="POST" class="flex-1 sm:flex-1 min-w-0">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <button 
                                type="submit" 
                                @if($product->stock <= 0) disabled @endif
                                class="w-full h-full py-3 px-4 bg-primary-800 hover:bg-primary-700 active:bg-primary-900 text-white text-base font-semibold rounded-xl shadow-md hover:shadow-xl transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-md disabled:hover:translate-y-0 whitespace-nowrap">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                    </svg>
                                    <span class="truncate">{{ __('product.add_to_cart') }}</span>
                                </span>
                            </button>
                        </form>

                        <!-- WhatsApp Buy Now Button Component -->
                        <x-whatsapp-buy-now :product="$product" :quantity="1" />
                    </div>
                </div>

                <!-- Extra Info -->
                <div class="border-t border-primary-200 dark:border-neutral-300 pt-4">
                    <div class="flex flex-col gap-3 text-xs text-neutral-600 dark:text-primary-300">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            <span class="font-medium">{{ __('product.free_shipping_threshold') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span class="font-medium">{{ __('product.secure_payment') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span class="font-medium">{{ __('product.return_policy') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" dir="{{ $dir }}">
        <div class="bg-white dark:bg-white rounded-2xl shadow-xl border border-neutral-200 dark:border-neutral-300 overflow-hidden">
            <!-- Tab Navigation -->
            <div class="border-b border-primary-200 dark:border-neutral-300">
                <nav class="flex gap-6 px-7" aria-label="Tabs">
                    <button onclick="switchTab('description')" class="tab-button py-4 px-3 border-b-3 border-primary-800 text-primary-800 dark:text-primary-800 font-bold text-base">
                        {{ __('product.tab_description') }}
                    </button>
                    <button onclick="switchTab('specifications')" class="tab-button py-4 px-3 border-b-3 border-transparent text-neutral-600 dark:text-neutral-600 hover:text-primary-800 dark:hover:text-primary-800 hover:border-primary-700 font-medium text-base transition-all">
                        {{ __('product.tab_specifications') }}
                    </button>
                    <button onclick="switchTab('shipping')" class="tab-button py-4 px-3 border-b-3 border-transparent text-neutral-600 dark:text-neutral-600 hover:text-primary-800 dark:hover:text-primary-800 hover:border-primary-700 font-medium text-base transition-all">
                        {{ __('product.tab_shipping') }}
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-8">
                <!-- Description Tab -->
                <div id="description-tab" class="tab-content">
                    <div class="prose prose-lg max-w-none text-neutral-600 dark:text-primary-300 leading-relaxed">
                        {!! nl2br(e($product->long_description ?? $product->description)) !!}
                    </div>
                </div>

                <!-- Specifications Tab -->
                <div id="specifications-tab" class="tab-content hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-base">
                            <tbody class="divide-y divide-primary-200 dark:divide-neutral-300">
                                <tr>
                                    <td class="py-3 text-neutral-500 dark:text-primary-300 font-bold w-1/3">{{ __('product.spec_material') }}</td>
                                    <td class="py-3 text-primary-800 dark:text-primary-800">Premium Cotton Blend</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-neutral-500 dark:text-primary-300 font-bold">{{ __('product.spec_dimensions') }}</td>
                                    <td class="py-3 text-primary-800 dark:text-primary-800">30cm x 40cm x 10cm</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-neutral-500 dark:text-primary-300 font-bold">{{ __('product.spec_weight') }}</td>
                                    <td class="py-3 text-primary-800 dark:text-primary-800">0.5 kg</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-neutral-500 dark:text-primary-300 font-bold">{{ __('product.spec_care') }}</td>
                                    <td class="py-3 text-primary-800 dark:text-primary-800">Machine washable at 30°C</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-neutral-500 dark:text-primary-300 font-bold">{{ __('product.spec_origin') }}</td>
                                    <td class="py-3 text-primary-800 dark:text-primary-800">Made in Europe</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Shipping Tab -->
                <div id="shipping-tab" class="tab-content hidden">
                    <div class="space-y-5 text-primary-800 dark:text-primary-800">
                        <div>
                            <h3 class="font-bold text-xl mb-2 text-primary-800 dark:text-primary-800">{{ __('product.shipping_title') }}</h3>
                            <p class="text-neutral-600 dark:text-primary-300 leading-relaxed">{{ __('product.shipping_info') }}</p>
                        </div>
                        <div>
                            <h3 class="font-bold text-xl mb-2 text-primary-800 dark:text-primary-800">{{ __('product.returns_title') }}</h3>
                            <p class="text-neutral-600 dark:text-primary-300 leading-relaxed">{{ __('product.returns_info') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section - Hidden for Later -->
    {{-- 
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white dark:bg-white rounded-2xl shadow-xl border border-neutral-200 dark:border-neutral-300 p-8">
            <h2 class="text-3xl font-bold text-primary-800 dark:text-primary-800 mb-6">Customer Reviews</h2>

            <!-- Reviews Summary -->
            <div class="flex items-center gap-6 mb-8 pb-8 border-b border-primary-200 dark:border-neutral-300">
                <div class="text-center bg-primary-100 dark:bg-primary-100 rounded-xl px-7 py-5">
                    <div class="text-5xl font-bold text-primary-800 dark:text-primary-800">{{ number_format($product->rating, 1) }}</div>
                    <div class="flex items-center justify-center mt-2.5">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-6 h-6 {{ $i <= floor($product->rating) ? 'text-warning' : 'text-neutral-300 dark:text-neutral-600' }} fill-current" viewBox="0 0 20 20">
                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                            </svg>
                        @endfor
                    </div>
                    <div class="text-sm text-neutral-500 dark:text-primary-300 mt-2 font-medium">{{ $reviews->count() }} reviews</div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="space-y-4 mb-8">
                @forelse($reviews as $review)
                    <div class="bg-primary-100 dark:bg-primary-100 rounded-xl p-5 border border-primary-200 dark:border-neutral-300">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-bold text-lg text-primary-800 dark:text-primary-800">{{ $review->user->name }}</h4>
                                <div class="flex items-center mt-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-warning' : 'text-neutral-300 dark:text-neutral-600' }} fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            <span class="text-sm text-neutral-500 dark:text-primary-300 font-medium">{{ $review->created_at->format('M d, Y') }}</span>
                        </div>
                        <p class="text-neutral-600 dark:text-primary-300 leading-relaxed">{{ $review->comment }}</p>
                    </div>
                @empty
                    <p class="text-center text-neutral-500 dark:text-primary-300 py-12 text-lg">No reviews yet. Be the first to review this product!</p>
                @endforelse
            </div>

            <!-- Review Form -->
            <div class="border-t border-primary-200 dark:border-neutral-300 pt-8">
                <h3 class="text-2xl font-bold text-primary-800 dark:text-primary-800 mb-5">Write a Review</h3>
                <form class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-primary-800 dark:text-primary-800 mb-2.5">Your Name</label>
                        <input type="text" class="w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-300 rounded-xl bg-white dark:bg-white text-primary-800 dark:text-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-800 focus:border-primary-800 transition-all" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-primary-800 dark:text-primary-800 mb-2.5">Rating</label>
                        <select class="w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-300 rounded-xl bg-white dark:bg-white text-primary-800 dark:text-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-800 focus:border-primary-800 transition-all" required>
                            <option value="">Select rating</option>
                            <option value="5">⭐⭐⭐⭐⭐ 5 Stars - Excellent</option>
                            <option value="4">⭐⭐⭐⭐ 4 Stars - Good</option>
                            <option value="3">⭐⭐⭐ 3 Stars - Average</option>
                            <option value="2">⭐⭐ 2 Stars - Poor</option>
                            <option value="1">⭐ 1 Star - Terrible</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-primary-800 dark:text-primary-800 mb-2.5">Your Review</label>
                        <textarea rows="5" class="w-full px-4 py-3 border-2 border-neutral-200 dark:border-neutral-300 rounded-xl bg-white dark:bg-white text-primary-800 dark:text-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-800 focus:border-primary-800 transition-all" required></textarea>
                    </div>
                    <button type="submit" class="px-7 py-2.5 bg-primary-800 hover:bg-primary-700 active:bg-primary-900 text-white text-base font-semibold rounded-xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                        Submit Review
                    </button>
                </form>
            </div>
        </div>
    </div>
    --}}

    <!-- Related Products -->
    @if($relatedProducts && $relatedProducts->count() > 0)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" dir="{{ $dir }}">
            <h2 class="text-4xl font-bold text-primary-800 dark:text-primary-800 mb-8">{{ __('product.related_products_title') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="bg-white dark:bg-white rounded-2xl shadow-md border border-neutral-200 dark:border-neutral-300 overflow-hidden hover:shadow-2xl hover:border-primary-800 hover:-translate-y-2 transition-all duration-300">
                        <a href="{{ route('products.show', $relatedProduct) }}">
                            <div class="aspect-square bg-primary-100 dark:bg-primary-100 overflow-hidden">
                                <img src="{{ $relatedProduct->primaryImage() ? asset('storage/' . $relatedProduct->primaryImage()->path) : asset('storage/images/placeholder.jpg') }}" alt="{{ $relatedProduct->name }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-primary-800 dark:text-primary-800 mb-2.5 line-clamp-2 hover:text-primary-700 transition-colors">{{ $relatedProduct->name }}</h3>
                                <div class="text-2xl font-bold text-primary-800 dark:text-primary-800 mb-3">€{{ number_format($relatedProduct->price, 2) }}</div>
                                <button class="w-full py-2 px-4 border-2 border-primary-800 dark:border-primary-800 text-primary-800 dark:text-primary-800 rounded-xl hover:bg-primary-800 hover:text-white dark:hover:bg-primary-800 dark:hover:text-white transition-all text-sm font-bold">
                                    {{ __('product.view_details') }}
                                </button>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- JavaScript for Interactive Elements -->
    <script>
        // Change main product image
        function changeMainImage(imageUrl) {
            document.getElementById('mainProductImage').src = imageUrl;
        }

        // Quantity controls
        function incrementQuantity() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        }

        function decrementQuantity() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        // Tab switching
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            // Remove active state from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-primary-800', 'text-primary-800', 'dark:text-primary-800', 'font-bold');
                btn.classList.add('border-transparent', 'text-neutral-600', 'dark:text-neutral-600', 'font-medium');
            });
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            // Add active state to clicked button
            event.target.classList.remove('border-transparent', 'text-neutral-600', 'dark:text-neutral-600', 'font-medium');
            event.target.classList.add('border-primary-800', 'text-primary-800', 'dark:text-primary-800', 'font-bold');
        }
    </script>
</x-app-layout>
