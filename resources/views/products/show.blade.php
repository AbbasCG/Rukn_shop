@php
    // Sizes & colours: data for the productOptions picker (resources/js/app.js).
    $product->loadMissing('variants');
    $hasVariants = $product->hasVariants();
    [$minPrice, $maxPrice] = $product->priceRange();
    $defaultPriceText = $minPrice < $maxPrice ? __('product.price_from', ['price' => money($minPrice)]) : money($minPrice);
    $variantOptions = $hasVariants ? [
        'sizes' => array_values($product->sizes ?? []),
        'sizeSystem' => $product->size_system,
        'colors' => collect($product->colors ?? [])->map(fn ($c) => [
            'key' => $c['key'], 'hex' => $c['hex'] ?? '#ffffff', 'name' => $product->colorName($c['key']),
        ])->values()->all(),
        'variants' => $product->variants->map(fn ($v) => [
            'id' => $v->id, 'size' => $v->size, 'color' => $v->color_key, 'stock' => (int) $v->stock,
            'priceText' => money($v->price ?? $product->price),
        ])->values()->all(),
        'defaultPriceText' => $defaultPriceText,
        'texts' => [
            'choose' => __('product.choose_options'),
            'outOfStock' => __('product.out_of_stock'),
            'inStock' => __('product.in_stock_available', ['count' => ':count']),
            'add' => __('product.add_to_cart'),
        ],
    ] : null;

    $seoImage = url($product->primaryImageUrl());
    $seoDescription = $product->short_description ?: $product->long_description;
    $productUrl = r('products.show', $product);

    // schema.org Product + BreadcrumbList, so search results can show price and availability.
    $structuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $seoDescription), 5000, ''),
            'image' => [$seoImage],
            'url' => $productUrl,
            'brand' => ['@type' => 'Brand', 'name' => __('seo.site_name')],
            'category' => $product->category?->display_name,
            // One price, or a range when sizes/colours are priced differently.
            'offers' => array_merge(
                $minPrice < $maxPrice
                    ? ['@type' => 'AggregateOffer', 'lowPrice' => number_format($minPrice, 2, '.', ''),
                       'highPrice' => number_format($maxPrice, 2, '.', ''), 'offerCount' => $product->variants->count()]
                    : ['@type' => 'Offer', 'price' => number_format($minPrice, 2, '.', '')],
                [
                    'url' => $productUrl,
                    'priceCurrency' => 'EUR',
                    'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'itemCondition' => 'https://schema.org/NewCondition',
                ],
            ),
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_filter([
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('home.nav_home'), 'item' => r('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __('seo.shop_title'), 'item' => r('products.index')],
                $product->category ? ['@type' => 'ListItem', 'position' => 3, 'name' => $product->category->display_name,
                    'item' => r('products.index', ['category' => $product->category->slug])] : null,
                ['@type' => 'ListItem', 'position' => $product->category ? 4 : 3, 'name' => $product->name],
            ])),
        ],
    ];
@endphp
@push('structured-data')
    <meta property="product:price:amount" content="{{ number_format($minPrice, 2, '.', '') }}">
    <meta property="product:price:currency" content="EUR">
    <meta property="product:availability" content="{{ $product->stock > 0 ? 'in stock' : 'out of stock' }}">
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush
<x-app-layout :title="$product->name" :description="$seoDescription" :image="$seoImage" type="product" :noindex="! $product->is_active">
    @unless($product->is_active)
        {{-- Only admins reach this page for a hidden product. --}}
        <div class="bg-amber-100 text-amber-900 text-sm text-center px-4 py-3">{{ __('product.hidden_notice') }}</div>
    @endunless
    <!-- Breadcrumbs -->
    <div class="bg-primary-50 dark:bg-primary-900 border-b border-primary-200 dark:border-primary-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav aria-label="Breadcrumb" class="text-sm text-neutral-600 dark:text-primary-200">
                <ol class="flex flex-wrap items-center gap-1.5">
                    <li>
                        <a href="{{ r('home') }}" class="inline-flex items-center px-2 py-1 rounded-md text-primary-700 dark:text-primary-100 hover:bg-primary-100 dark:hover:bg-primary-700 hover:text-primary-900 dark:hover:text-white transition-all duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-primary-300">
                            {{ __('product.breadcrumb_home') }}
                        </a>
                    </li>
                    <li class="text-neutral-400 dark:text-primary-300" aria-hidden="true">/</li>
                    <li>
                        <a href="{{ r('products.index') }}" class="inline-flex items-center px-2 py-1 rounded-md text-primary-700 dark:text-primary-100 hover:bg-primary-100 dark:hover:bg-primary-700 hover:text-primary-900 dark:hover:text-white transition-all duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-primary-300">
                            {{ __('product.breadcrumb_products') }}
                        </a>
                    </li>
                    @if($product->category)
                        <li class="text-neutral-400 dark:text-primary-300" aria-hidden="true">/</li>
                        <li>
                            <a href="{{ r('categories.show', ['category' => $product->category]) }}" class="inline-flex items-center px-2 py-1 rounded-md text-primary-700 dark:text-primary-100 hover:bg-primary-100 dark:hover:bg-primary-700 hover:text-primary-900 dark:hover:text-white hover:underline transition-all duration-200 font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-300">
                                {{ $product->category->display_name }}
                            </a>
                        </li>
                    @endif
                    <li class="text-neutral-400 dark:text-primary-300" aria-hidden="true">/</li>
                    <li class="px-2 py-1 text-primary-900 dark:text-white font-semibold" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Product Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
            <!-- Left Column - Image Gallery -->
            <div class="space-y-4 h-full">
                <!-- Main Image -->
                <div class="w-full bg-primary-100 dark:bg-primary-100 rounded-2xl overflow-hidden shadow-xl border border-neutral-200 dark:border-neutral-300" style="aspect-ratio: 4/5;">
                    <img 
                        id="mainProductImage" 
                        src="{{ $product->primaryImageUrl() }}" 
                        alt="{{ $product->name }}" 
                        class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                        onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'"
                    >
                </div>

                <!-- Thumbnail Gallery -->
                @if($product->images && $product->images->count() > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($product->images as $index => $image)
                            <button 
                                data-image="{{ $product->imageUrlFromPath($image->path) }}"
                                onclick="changeMainImage(this.dataset.image)" 
                                class="aspect-square bg-primary-100 dark:bg-primary-100 rounded-xl overflow-hidden border-2 {{ $image->is_primary ? 'border-primary-800' : 'border-neutral-200 dark:border-neutral-300' }} hover:border-primary-800 dark:hover:border-primary-800 transition-all cursor-pointer">
                                <img src="{{ $product->imageUrlFromPath($image->path) }}" alt="{{ $product->name }} - Image {{ $index + 1 }}" class="w-full h-full object-cover" onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right Column - Product Info -->
            <div class="space-y-5" @if($hasVariants) x-data="productOptions(@js($variantOptions))" @endif>
                <!-- Category Badge -->
                @if($product->category)
                    <div class="inline-block">
                        <span class="px-3 py-1 bg-primary-100 dark:bg-primary-700 text-primary-800 dark:text-primary-50 text-xs font-bold uppercase tracking-wider rounded-full">{{ $product->category->display_name }}</span>
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
                <div class="text-5xl font-bold text-primary-800 dark:text-primary-800" @if($hasVariants) x-text="priceText" @endif>{{ $defaultPriceText }}</div>

                <!-- Availability -->
                @if($hasVariants)
                    <div x-show="selected" x-cloak class="flex items-center gap-2 px-4 py-1.5 rounded-lg font-bold"
                         :class="selected && selected.stock > 0 ? 'text-success bg-success-light/20' : 'text-error bg-error-light/20'">
                        <span x-text="stockText"></span>
                    </div>
                @elseif($product->stock > 0)
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
                <p class="text-base text-neutral-600 dark:text-primary-300 leading-relaxed"><bdi>{{ $product->short_description }}</bdi></p>

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
                            <span class="font-bold text-primary-800 dark:text-primary-800">{{ $product->category->display_name }}</span>
                        </div>
                    @endif
                </div> -->

                <!-- Options: size & colour -->
                @if($hasVariants)
                    <div class="space-y-5">
                        @if(!empty($variantOptions['sizes']))
                            <div>
                                <p class="text-sm font-semibold text-primary-800 mb-2">
                                    {{ __('product.size') }}<span x-show="size" x-cloak>: <span dir="ltr" x-text="size"></span></span>
                                </p>
                                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('product.size') }}">
                                    <template x-for="option in sizes" :key="option">
                                        <button type="button" role="radio" :aria-checked="(size === option).toString()"
                                                @click="size = (size === option ? null : option)"
                                                :disabled="!sizeAvailable(option)"
                                                :class="size === option ? 'border-primary-800 bg-primary-800 text-white' : 'border-primary-200 bg-white text-primary-800 hover:border-primary-800'"
                                                class="min-w-12 h-11 px-3 rounded-xl border text-sm font-semibold transition-colors disabled:opacity-40 disabled:line-through disabled:cursor-not-allowed">
                                            <span dir="ltr" x-text="option"></span>
                                        </button>
                                    </template>
                                </div>
                                @if($product->size_system === 'lengths')
                                    <p class="mt-2 text-xs text-neutral-500">{{ __('product.size_hint_lengths') }}</p>
                                @endif
                            </div>
                        @endif

                        @if(!empty($variantOptions['colors']))
                            <div>
                                <p class="text-sm font-semibold text-primary-800 mb-2">
                                    {{ __('product.color') }}<span x-show="color" x-cloak>: <span x-text="colorName"></span></span>
                                </p>
                                <div class="flex flex-wrap gap-3" role="radiogroup" aria-label="{{ __('product.color') }}">
                                    <template x-for="option in colors" :key="option.key">
                                        <button type="button" role="radio" :aria-checked="(color === option.key).toString()"
                                                :aria-label="option.name" :title="option.name"
                                                @click="color = (color === option.key ? null : option.key)"
                                                :disabled="!colorAvailable(option.key)"
                                                :class="color === option.key ? 'ring-2 ring-offset-2 ring-primary-800' : 'ring-1 ring-primary-200'"
                                                class="relative h-10 w-10 rounded-full transition disabled:opacity-60 disabled:cursor-not-allowed">
                                            <span class="absolute inset-1 rounded-full border border-black/10" :style="`background:${option.hex}`"></span>
                                            {{-- Unavailable: keep the real colour visible and strike it through. --}}
                                            <span x-show="!colorAvailable(option.key)" class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
                                                <span class="block h-0.5 w-11 rotate-45 bg-white ring-1 ring-primary-800/60"></span>
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Quantity + Actions -->
                <div class="space-y-3 pt-4">
                    <div class="flex items-stretch gap-3">
                        <!-- Quantity Selector (submitted with the add-to-cart form via form="") -->
                        <div class="inline-flex items-center h-12 rounded-xl border border-primary-200 bg-white flex-shrink-0">
                            <button type="button" onclick="decrementQuantity()" aria-label="−" class="w-10 h-full flex items-center justify-center text-primary-800 hover:bg-primary-50 rounded-s-xl transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                form="add-to-cart-form"
                                value="1"
                                min="1"
                                max="{{ max(1, $product->stock) }}"
                                @if($hasVariants) :max="maxQty" @endif
                                aria-label="{{ __('product.quantity_label') }}"
                                class="w-10 h-full text-center p-0 border-0 bg-transparent text-sm font-bold text-primary-800 focus:ring-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                            >
                            <button type="button" onclick="incrementQuantity()" aria-label="+" class="w-10 h-full flex items-center justify-center text-primary-800 hover:bg-primary-50 rounded-e-xl transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Add to Cart Button -->
                        <form id="add-to-cart-form" action="{{ r('cart.store') }}" method="POST" class="flex-1 min-w-0">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @if($hasVariants)
                                <input type="hidden" name="variant_id" :value="selected ? selected.id : ''">
                                <input type="hidden" id="selected-variant-label" :value="label">
                            @endif
                            <button
                                type="submit"
                                @disabled($product->stock <= 0)
                                @if($hasVariants) :disabled="!canAdd" @endif
                                class="w-full h-12 px-4 inline-flex items-center justify-center gap-2 bg-primary-800 hover:bg-primary-700 active:bg-primary-900 text-white text-base font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                </svg>
                                <span @if($hasVariants) x-text="buttonText" @endif>{{ $hasVariants ? __('product.choose_options') : ($product->stock > 0 ? __('product.add_to_cart') : __('product.out_of_stock')) }}</span>
                            </button>
                        </form>
                    </div>

                    <!-- WhatsApp: secondary action -->
                    <x-whatsapp-buy-now :product="$product" :quantity="1" />
                </div>

                <!-- Extra Info -->
                <div class="border-t border-primary-200 dark:border-neutral-300 pt-4">
                    <div class="flex flex-col gap-3 text-xs text-neutral-600 dark:text-primary-300">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            <span class="font-medium">{{ policy_text('product.free_shipping_threshold') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span class="font-medium">{{ policy_text(\App\Support\Payments::textKey('product.secure_payment')) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-800 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span class="font-medium">{{ policy_text('product.return_policy') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white dark:bg-white rounded-2xl shadow-xl border border-neutral-200 dark:border-neutral-300 overflow-hidden">
            <!-- Tab Navigation -->
            <div class="border-b border-primary-200 dark:border-neutral-300">
                <nav class="flex flex-wrap gap-3 px-7" aria-label="Tabs">
                    <button onclick="switchTab('description', event)" class="tab-button py-3 px-4 border-b-2 border-primary-800 text-primary-800 dark:text-primary-800 font-bold text-base transition-all">
                        {{ __('product.tab_description') }}
                    </button>
                    <button onclick="switchTab('specifications', event)" class="tab-button py-3 px-4 border-b-2 border-transparent text-neutral-600 dark:text-neutral-600 hover:text-primary-800 dark:hover:text-primary-800 hover:border-primary-700 font-medium text-base transition-all">
                        {{ __('product.tab_specifications') }}
                    </button>
                    <button onclick="switchTab('shipping', event)" class="tab-button py-3 px-4 border-b-2 border-transparent text-neutral-600 dark:text-neutral-600 hover:text-primary-800 dark:hover:text-primary-800 hover:border-primary-700 font-medium text-base transition-all">
                        {{ __('product.tab_shipping') }}
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-8">
                <!-- Description Tab -->
                <div id="description-tab" class="tab-content transition-opacity duration-300 opacity-100">
                    <div dir="auto" class="prose prose-lg max-w-none text-neutral-600 dark:text-primary-300 leading-relaxed text-start">
                        {!! nl2br(e($product->long_description)) !!}
                    </div>
                </div>

                <!-- Specifications Tab -->
                <div id="specifications-tab" class="tab-content transition-opacity duration-300 opacity-0 hidden">
                    @php
                        $specifications = collect($product->specifications ?? [])
                            ->filter(function ($spec) {
                                return is_array($spec)
                                    && isset($spec['key'], $spec['value'])
                                    && trim((string) $spec['key']) !== ''
                                    && trim((string) $spec['value']) !== '';
                            })
                            ->values();
                    @endphp

                    @if($specifications->isEmpty())
                        <div class="rounded-2xl border border-primary-200 dark:border-neutral-300 bg-primary-50/60 dark:bg-primary-100/40 p-6 text-start">
                            <p class="text-sm font-semibold text-primary-800 dark:text-primary-800">{{ __('product.specifications_empty') }}</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($specifications as $spec)
                                @php
                                    $specKey = trim((string) $spec['key']);
                                    $specValue = trim((string) $spec['value']);
                                    $normalizedKey = \Illuminate\Support\Str::lower($specKey);

                                    $iconType = 'default';
                                    if (str_contains($normalizedKey, 'material') || str_contains($normalizedKey, 'fabric')) {
                                        $iconType = 'material';
                                    } elseif (str_contains($normalizedKey, 'weight')) {
                                        $iconType = 'weight';
                                    } elseif (str_contains($normalizedKey, 'dimension') || str_contains($normalizedKey, 'size')) {
                                        $iconType = 'dimensions';
                                    } elseif (str_contains($normalizedKey, 'care') || str_contains($normalizedKey, 'wash')) {
                                        $iconType = 'care';
                                    } elseif (str_contains($normalizedKey, 'origin') || str_contains($normalizedKey, 'country')) {
                                        $iconType = 'origin';
                                    }
                                @endphp

                                <div class="rounded-2xl border border-neutral-200 dark:border-neutral-300 bg-white dark:bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex items-start gap-3 ">
                                        <div class="h-10 w-10 rounded-xl bg-primary-100 dark:bg-primary-100 flex items-center justify-center text-primary-800 flex-shrink-0">
                                            @if($iconType === 'material')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8l8-4 8 4-8 4-8-4zm0 8l8 4 8-4m-16-4l8 4 8-4" />
                                                </svg>
                                            @elseif($iconType === 'weight')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12l-1 13H7L6 7zm3-3h6l1 3H8l1-3z" />
                                                </svg>
                                            @elseif($iconType === 'dimensions')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 17h18M7 3v18" />
                                                </svg>
                                            @elseif($iconType === 'care')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l9 4.5-9 4.5-9-4.5L12 3zm0 9v9" />
                                                </svg>
                                            @elseif($iconType === 'origin')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a7 7 0 00-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 00-7-7zm0 9a2 2 0 110-4 2 2 0 010 4z" />
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-8 4h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs uppercase tracking-wide text-neutral-500 dark:text-primary-300 font-semibold">
                                                {{ $specKey }}
                                            </p>
                                            <p class="text-base font-semibold text-primary-800 dark:text-primary-800 break-words">
                                                {{ $specValue }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Shipping Tab -->
                <div id="shipping-tab" class="tab-content transition-opacity duration-300 opacity-0 hidden">
                    <div class="space-y-5 text-primary-800 dark:text-primary-800">
                        <div>
                            <h3 class="font-bold text-xl mb-2 text-primary-800 dark:text-primary-800">{{ __('product.shipping_title') }}</h3>
                            <p class="text-neutral-600 dark:text-primary-300 leading-relaxed">{{ policy_text('product.shipping_info') }}</p>
                        </div>
                        <div>
                            <h3 class="font-bold text-xl mb-2 text-primary-800 dark:text-primary-800">{{ __('product.returns_title') }}</h3>
                            <p class="text-neutral-600 dark:text-primary-300 leading-relaxed">{{ policy_text('product.returns_info') }}</p>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h2 class="text-2xl md:text-3xl font-bold text-primary-800 dark:text-primary-800 mb-6">{{ __('product.related_products_title') }}</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-8 lg:gap-x-6">
                @foreach($relatedProducts as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
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

        function openWhatsAppChat(event, button) {
            if (!button || button.getAttribute('aria-disabled') === 'true') {
                event.preventDefault();
                return false;
            }

            const quantityInput = document.getElementById('quantity');
            let quantity = quantityInput ? parseInt(quantityInput.value, 10) : 1;
            if (!Number.isFinite(quantity) || quantity < 1) {
                quantity = 1;
            }

            const phoneNumber = button.dataset.whatsappNumber;
            if (!phoneNumber) {
                event.preventDefault();
                return false;
            }

            const productName = button.dataset.productName || '';
            const productPrice = button.dataset.productPrice || '0,00';
            const greetingLabel = button.dataset.labelGreeting || '';
            const productLabel = button.dataset.labelProduct || 'Product';
            const priceLabel = button.dataset.labelPrice || 'Price';
            const quantityLabel = button.dataset.labelQuantity || 'Quantity';
            const viewProductLabel = button.dataset.labelViewProduct || 'Product link:';
            const productUrl = window.location.href;

            // Chosen size/colour, if the product has them.
            const variantLabel = document.getElementById('selected-variant-label')?.value || '';
            const variantLine = variantLabel ? `*${@js(__('product.variant'))}:* ${variantLabel}\n` : '';
            const message = `*${greetingLabel}*\n\n*${productLabel}:* ${productName}\n${variantLine}*${priceLabel}:* €${productPrice}\n*${quantityLabel}:* ${quantity}\n\n${viewProductLabel}\n${productUrl}`;
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;

            window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
            event.preventDefault();
            return false;
        }

        // Tab switching
        function switchTab(tabName, event) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('opacity-0');
                tab.classList.add('hidden');
            });
            // Remove active state from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-primary-800', 'text-primary-800', 'dark:text-primary-800', 'font-bold');
                btn.classList.add('border-transparent', 'text-neutral-600', 'dark:text-neutral-600', 'font-medium');
            });
            // Show selected tab
            const selectedTab = document.getElementById(tabName + '-tab');
            selectedTab.classList.remove('hidden');
            requestAnimationFrame(() => {
                selectedTab.classList.remove('opacity-0');
            });
            // Add active state to clicked button
            const target = event?.currentTarget || event?.target;
            if (target) {
                target.classList.remove('border-transparent', 'text-neutral-600', 'dark:text-neutral-600', 'font-medium');
                target.classList.add('border-primary-800', 'text-primary-800', 'dark:text-primary-800', 'font-bold');
            }
        }
    </script>
</x-app-layout>
