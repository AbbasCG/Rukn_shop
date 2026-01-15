<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-light via-white to-primary-gray/30" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            
            <!-- Language Switcher (Top Right) -->
            <div class="flex justify-end {{ app()->getLocale() === 'ar' ? 'rtl:justify-start' : '' }}">
                <x-language-switcher :currentLocale="app()->getLocale()" />
            </div>
            
            <!-- A) SHOP HERO STRIP -->
            <div class="relative overflow-hidden rounded-3xl border border-primary-gray/60 bg-gradient-to-r from-primary-light via-white to-primary-gray/50 shadow-sm">
                <div class="relative px-6 py-8 md:px-10 md:py-10">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                        <!-- Left: Title & Badge -->
                        <div class="space-y-3 flex-1">
                            <div class="inline-flex items-center px-3 py-1 rounded-full bg-primary-dark/5 border border-primary-dark/10">
                                <span class="text-xs font-semibold text-primary-dark uppercase tracking-wider">{{ __('shop.shop_badge') }}</span>
                            </div>
                            <h1 class="text-4xl md:text-5xl font-bold text-primary-dark leading-tight">
                                {!! __('shop.shop_title') !!}
                            </h1>
                            <p class="text-neutral-600 text-sm md:text-base max-w-md">
                                {{ __('shop.shop_subtitle') }}
                            </p>
                        </div>
                        
                        <!-- Right: Quick Stats Chips (desktop) -->
                        <div class="hidden md:flex flex-col gap-2.5">
                            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/80 border border-primary-gray/40 shadow-sm">
                                <svg class="w-4 h-4 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-sm font-medium text-primary-dark">{{ __('shop.fast_shipping') }}</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/80 border border-primary-gray/40 shadow-sm">
                                <svg class="w-4 h-4 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                <span class="text-sm font-medium text-primary-dark">{{ __('shop.easy_returns') }}</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/80 border border-primary-gray/40 shadow-sm">
                                <svg class="w-4 h-4 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span class="text-sm font-medium text-primary-dark">{{ __('shop.secure_checkout') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- B) FILTER DRAWER EXPERIENCE -->
            <div x-data="{ 
                filtersOpen: false,
                min: '{{ request('min_price') }}', 
                max: '{{ request('max_price') }}' 
            }" class="space-y-4">
                
                <!-- Top Row: Search + Filters Button + Sort -->
                <div class="flex flex-col sm:flex-row gap-3 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                    <!-- Search Input (Prominent) -->
                    <form method="GET" action="{{ route('products.index') }}" class="flex-1">
                        <label for="q" class="sr-only">{{ __('shop.search_products') }}</label>
                        <div class="relative">
                            <input 
                                id="q" 
                                type="text" 
                                name="q" 
                                value="{{ request('q') }}" 
                                placeholder="{{ __('shop.search_placeholder') }}" 
                                class="w-full rounded-2xl border border-primary-gray/60 bg-white {{ app()->getLocale() === 'ar' ? 'pr-11 pl-4' : 'pl-11 pr-4' }} py-3.5 text-sm placeholder-neutral-400 focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition shadow-sm"
                            />
                            <svg class="absolute {{ app()->getLocale() === 'ar' ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request('min_price'))
                            <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                        @endif
                        @if(request('max_price'))
                            <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif
                    </form>

                    <!-- Right: Filters + Sort Buttons -->
                    <div class="flex gap-3 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                        <!-- Filters Button -->
                        <button 
                            type="button"
                            @click="filtersOpen = !filtersOpen"
                            class="inline-flex items-center gap-2 px-5 py-3.5 rounded-2xl border border-primary-gray/60 bg-white hover:bg-primary-gray/30 text-primary-dark font-medium text-sm transition shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            {{ __('shop.filters') }}
                            @if(request('category') || request('min_price') || request('max_price'))
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-dark text-white text-xs font-bold">
                                    {{ collect([request('category'), request('min_price'), request('max_price')])->filter()->count() }}
                                </span>
                            @endif
                        </button>

                        <!-- Sort Dropdown -->
                        <form method="GET" action="{{ route('products.index') }}" class="inline-block">
                            <select 
                                name="sort" 
                                onchange="this.form.submit()"
                                class="px-5 py-3.5 rounded-2xl border border-primary-gray/60 bg-white text-primary-dark font-medium text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition shadow-sm appearance-none {{ app()->getLocale() === 'ar' ? 'pl-10' : 'pr-10' }}"
                                style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 24 24%27 stroke=%27%231F1D20%27%3E%3Cpath stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%272%27 d=%27M19 9l-7 7-7-7%27/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }} 0.75rem center; background-size: 1.25rem;"
                            >
                                <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>{{ __('shop.sort_newest') }}</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>{{ __('shop.sort_price_low') }}</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>{{ __('shop.sort_price_high') }}</option>
                                <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>{{ __('shop.sort_popular') }}</option>
                                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ __('shop.sort_name') }}</option>
                            </select>
                            @if(request('q'))
                                <input type="hidden" name="q" value="{{ request('q') }}">
                            @endif
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            @if(request('min_price'))
                                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            @endif
                            @if(request('max_price'))
                                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Filter Panel (Slide Down) -->
                <div 
                    x-show="filtersOpen" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="bg-white rounded-2xl border border-primary-gray/60 shadow-md p-6"
                    style="display: none;"
                >
                    <form method="GET" action="{{ route('products.index') }}" class="space-y-5">
                        <div class="flex items-center justify-between mb-4 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <h3 class="text-lg font-bold text-primary-dark">{{ __('shop.filters') }}</h3>
                            <button type="button" @click="filtersOpen = false" class="text-neutral-400 hover:text-neutral-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-semibold text-primary-dark mb-2">{{ __('shop.filter_category') }}</label>
                                <select 
                                    id="category" 
                                    name="category" 
                                    class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition"
                                >
                                    <option value="">{{ __('shop.filter_all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Min Price -->
                            <div>
                                <label for="min_price" class="block text-sm font-semibold text-primary-dark mb-2">{{ __('shop.filter_min_price') }}</label>
                                <input 
                                    type="number" 
                                    id="min_price" 
                                    name="min_price" 
                                    x-model="min"
                                    min="0" 
                                    step="0.01"
                                    placeholder="0.00" 
                                    class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition"
                                />
                            </div>

                            <!-- Max Price -->
                            <div>
                                <label for="max_price" class="block text-sm font-semibold text-primary-dark mb-2">{{ __('shop.filter_max_price') }}</label>
                                <input 
                                    type="number" 
                                    id="max_price" 
                                    name="max_price" 
                                    x-model="max"
                                    min="0" 
                                    step="0.01"
                                    placeholder="1000.00" 
                                    class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition"
                                />
                            </div>
                        </div>

                        <!-- Apply & Reset Buttons -->
                        <div class="flex items-center gap-3 pt-2 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <button 
                                type="submit" 
                                class="flex-1 px-6 py-3 rounded-xl bg-primary-dark hover:bg-primary-dark/90 text-white font-semibold text-sm shadow-sm hover:shadow-md transition duration-200 active:scale-95"
                            >
                                {{ __('shop.apply_filters') }}
                            </button>
                            <a 
                                href="{{ route('products.index') }}" 
                                class="px-6 py-3 rounded-xl border border-primary-gray/60 text-primary-dark hover:bg-primary-gray/30 font-semibold text-sm transition duration-200"
                            >
                                {{ __('shop.reset') }}
                            </a>
                        </div>

                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif
                    </form>
                </div>
            </div>

            <!-- D) RESULTS HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-1 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                <div class="text-sm text-neutral-600">
                    {{ __('shop.showing') }} <span class="font-bold text-primary-dark">{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</span> {{ __('shop.of') }} <span class="font-bold text-primary-dark">{{ $products->total() }}</span> {{ __('shop.products') }}
                    @if(request('category'))
                        <span class="text-neutral-500">{{ __('shop.in') }}</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary-dark/5 text-primary-dark text-xs font-semibold {{ app()->getLocale() === 'ar' ? 'mr-1' : 'ml-1' }}">
                            {{ $categories->firstWhere('slug', request('category'))->name ?? request('category') }}
                        </span>
                    @endif
                    @if(request('q'))
                        <span class="text-neutral-500">{{ __('shop.matching') }}</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary-dark/5 text-primary-dark text-xs font-semibold {{ app()->getLocale() === 'ar' ? 'mr-1' : 'ml-1' }}">
                            "{{ request('q') }}"
                        </span>
                    @endif
                </div>
                @if(request('q') || request('category') || request('min_price') || request('max_price'))
                    <a 
                        href="{{ route('products.index') }}" 
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-dark hover:text-primary-dark/70 transition {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ __('shop.clear_all_filters') }}
                    </a>
                @endif
            </div>

            <!-- E) PRODUCTS GRID / EMPTY STATE -->
            @if($products->count() === 0)
                <!-- Empty State -->
                <div class="bg-white rounded-3xl border border-primary-gray/60 shadow-sm p-12 md:p-16 text-center">
                    <div class="mx-auto w-20 h-20 rounded-2xl bg-primary-gray/50 flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-primary-dark mb-2">{{ __('shop.no_products_found') }}</h3>
                    <p class="text-neutral-600 mb-6 max-w-md mx-auto">
                        {{ __('shop.no_products_message') }}
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                        @if(request()->hasAny(['q', 'category', 'min_price', 'max_price']))
                            <a 
                                href="{{ route('products.index') }}" 
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary-dark hover:bg-primary-dark/90 text-white font-semibold text-sm shadow-sm transition duration-200 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ __('shop.clear_filters') }}
                            </a>
                        @endif
                        <a 
                            href="{{ route('products.index') }}" 
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-primary-gray/60 text-primary-dark hover:bg-primary-gray/30 font-semibold text-sm transition duration-200 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}"
                        >
                            {{ __('shop.back_to_shop') }}
                        </a>
                    </div>
                </div>
            @else
                <!-- C) PRODUCT GRID -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    @foreach($products as $product)
                        @php
                            $thumb = optional($product->images->first())->path;
                            $imageUrl = $thumb ? asset('storage/' . $thumb) : asset('images/placeholder.jpg');
                            $productCategory = $categories->firstWhere('id', $product->category_id);
                        @endphp
                        <a href="{{ route('products.show', $product) }}" class="group flex flex-col bg-white rounded-3xl border border-primary-gray/40 overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                            <!-- Image with Soft Frame -->
                            <div class="block relative overflow-hidden bg-neutral-50">
                                <div class="relative aspect-[3/4]">
                                    <img 
                                        src="{{ $imageUrl }}" 
                                        alt="{{ $product->name }}" 
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    />
                                    
                                    <!-- Stock Badge Overlay -->
                                    @if(($product->stock ?? 0) <= 0)
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center pointer-events-none">
                                            <span class="px-4 py-2 rounded-xl bg-white text-primary-dark text-sm font-bold shadow-lg">
                                                {{ __('shop.out_of_stock') }}
                                            </span>
                                        </div>
                                    @elseif(($product->stock ?? 0) > 0 && ($product->stock ?? 0) <= 5)
                                        <span class="absolute top-3 {{ app()->getLocale() === 'ar' ? 'left-3' : 'right-3' }} px-3 py-1.5 rounded-xl bg-amber-500 text-white text-xs font-bold shadow-md pointer-events-none">
                                            {{ __('shop.only_left') }} {{ $product->stock }} {{ __('shop.left') }}
                                        </span>
                                    @endif

                                    <!-- Hover Overlay -->
                                    <div class="absolute inset-0 bg-primary-dark/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

                                    <!-- Hover Action Buttons -->
                                    <div class="absolute inset-x-0 bottom-4 flex items-center justify-center gap-3 px-4 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 md:block hidden">
                                        <!-- Add to Cart -->
                                        <div x-data="{ added: false }" class="inline-block">
                                            <button 
                                                type="button"
                                                @click.stop.prevent="
                                                    const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                                                    fetch('{{ route('cart.store') }}', {
                                                        method: 'POST',
                                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                                                        body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                                                    }).then(r => r.json()).then(d => { 
                                                        if (d && d.ok) { 
                                                            $store.cart.count = d.count; 
                                                            added = true; 
                                                            setTimeout(() => added = false, 1200); 
                                                        } 
                                                    });
                                                "
                                                :class="added ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white border-primary-gray/60 text-primary-dark hover:bg-primary-dark hover:border-primary-dark hover:text-white'"
                                                class="inline-flex items-center justify-center h-10 w-10 rounded-full border shadow-md transition-all duration-200 hover:scale-105 hover:shadow-lg active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                                @if(($product->stock ?? 0) <= 0) disabled @endif
                                                title="{{ __('shop.add_to_cart') }}"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- View Product -->
                                        <button 
                                            type="button"
                                            @click.stop="window.location.href='{{ route('products.show', $product) }}'"
                                            class="inline-flex items-center justify-center h-10 w-10 rounded-full border border-primary-gray/60 bg-white text-primary-dark shadow-md transition-all duration-200 hover:bg-primary-dark hover:border-primary-dark hover:text-white hover:scale-105 hover:shadow-lg active:scale-95"
                                            title="{{ __('shop.view_product') }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>

                                        <!-- Wishlist -->
                                        <button 
                                            type="button"
                                            @click.stop.prevent
                                            class="inline-flex items-center justify-center h-10 w-10 rounded-full border border-primary-gray/60 bg-white text-primary-dark shadow-md transition-all duration-200 hover:bg-primary-dark hover:border-primary-dark hover:text-white hover:scale-105 hover:shadow-lg active:scale-95"
                                            title="{{ __('shop.add_to_wishlist') }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="p-4 flex flex-col flex-1 space-y-3">
                                <!-- Category Pill -->
                                @if($productCategory)
                                    <span class="inline-flex items-center self-start px-2.5 py-1 rounded-lg bg-primary-gray/60 text-primary-dark text-xs font-semibold">
                                        {{ $productCategory->name }}
                                    </span>
                                @endif

                                <!-- Product Name -->
                                <h3 class="text-sm font-bold text-primary-dark line-clamp-2 transition leading-snug">
                                    {{ $product->name }}
                                </h3>

                                <!-- Price + Stock Status Row -->
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-xl font-bold text-primary-dark">
                                        €{{ number_format($product->price, 2) }}
                                    </span>
                                    @if(($product->stock ?? 0) > 5)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ __('shop.in_stock') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                                <!-- Mobile: Add to Cart Button (visible on small screens) -->
                                <div class="px-4 pb-4 md:hidden" x-data="{ added: false }">
                                    <button 
                                        type="button"
                                        @click.stop.prevent="
                                            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                                            fetch('{{ route('cart.store') }}', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                                                body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                                            }).then(r => r.json()).then(d => { 
                                                if (d && d.ok) { 
                                                    $store.cart.count = d.count; 
                                                    added = true; 
                                                    setTimeout(() => added = false, 1500); 
                                                } 
                                            });
                                        "
                                        :class="added ? 'bg-emerald-600' : 'bg-primary-dark hover:bg-primary-dark/90'"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-bold transition duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                        @if(($product->stock ?? 0) <= 0) disabled @endif
                                    >
                                        <span x-show="!added" class="inline-flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            {{ __('shop.add_to_cart') }}
                                        </span>
                                        <span x-show="added" x-cloak class="inline-flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ __('shop.added') }}
                                        </span>
                                    </button>
                                </div>
                        </a>
                    @endforeach
                </div>

                <!-- F) PAGINATION -->
                <div class="pt-8">
                    <div class="flex justify-center">
                        <div class="inline-flex rounded-2xl border border-primary-gray/60 bg-white shadow-sm overflow-hidden">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>