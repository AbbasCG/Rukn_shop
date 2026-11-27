@php
    $activeCategory = filled(request('category')) ? $categories->firstWhere('slug', request('category')) : null;
@endphp
<x-app-layout
    :title="$activeCategory?->display_name ?? __('seo.shop_title')"
    :description="$activeCategory
        ? policy_text('seo.category_description', ['category' => $activeCategory->display_name])
        : policy_text('seo.shop_description')"
>
    <div class="min-h-screen bg-gradient-to-br from-primary-light via-white to-primary-gray/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            
            <!-- A) PAGE HEADER -->
            <header class="space-y-2 pt-2">
                <h1 class="text-3xl md:text-4xl font-bold text-primary-dark">{{ __('shop.shop_title') }}</h1>
                <p class="text-neutral-600 max-w-xl">{{ __('shop.shop_subtitle') }}</p>
            </header>

            <!-- B) FILTER DRAWER EXPERIENCE -->
            <div x-data="{ 
                filtersOpen: false,
                min: '{{ request('min_price') }}', 
                max: '{{ request('max_price') }}' 
            }" class="space-y-4">
                
                <!-- Top Row: Search + Filters Button + Sort -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search Input (Prominent) -->
                    <form method="GET" action="{{ r('products.index') }}" class="flex-1">
                        <label for="q" class="sr-only">{{ __('shop.search_products') }}</label>
                        <div class="relative">
                            <input 
                                id="q" 
                                type="text" 
                                name="q" 
                                value="{{ request('q') }}" 
                                placeholder="{{ __('shop.search_placeholder') }}" 
                                class="w-full rounded-2xl border border-primary-gray/60 bg-white ps-11 pe-4 py-3.5 text-sm placeholder-neutral-400 focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition shadow-sm"
                            />
                            <svg class="absolute start-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <div class="flex gap-3">
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
                        <form method="GET" action="{{ r('products.index') }}" class="inline-block">
                            <select 
                                name="sort" 
                                onchange="this.form.submit()"
                                class="px-5 py-3.5 rounded-2xl border border-primary-gray/60 bg-white text-primary-dark font-medium text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition shadow-sm appearance-none pe-10"
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
                    <form method="GET" action="{{ r('products.index') }}" class="space-y-5">
                        <div class="flex items-center justify-between mb-4">
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
                                            {{ $category->display_name }}
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
                        <div class="flex items-center gap-3 pt-2">
                            <button 
                                type="submit" 
                                class="flex-1 px-6 py-3 rounded-xl bg-primary-dark hover:bg-primary-dark/90 text-white font-semibold text-sm shadow-sm hover:shadow-md transition duration-200 active:scale-95"
                            >
                                {{ __('shop.apply_filters') }}
                            </button>
                            <a 
                                href="{{ r('products.index') }}" 
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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-1">
                <div class="text-sm text-neutral-600">
                    {{ __('shop.showing') }} <span class="font-bold text-primary-dark">{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</span> {{ __('shop.of') }} <span class="font-bold text-primary-dark">{{ $products->total() }}</span> {{ __('shop.products') }}
                    @if(request('category'))
                        <span class="text-neutral-500">{{ __('shop.in') }}</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary-dark/5 text-primary-dark text-xs font-semibold ms-1">
                            {{ $categories->firstWhere('slug', request('category'))->name ?? request('category') }}
                        </span>
                    @endif
                    @if(request('q'))
                        <span class="text-neutral-500">{{ __('shop.matching') }}</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary-dark/5 text-primary-dark text-xs font-semibold ms-1">
                            "{{ request('q') }}"
                        </span>
                    @endif
                </div>
                @if(request('q') || request('category') || request('min_price') || request('max_price'))
                    <a 
                        href="{{ r('products.index') }}" 
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-dark hover:text-primary-dark/70 transition"
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
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        @if(request()->hasAny(['q', 'category', 'min_price', 'max_price']))
                            <a 
                                href="{{ r('products.index') }}" 
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary-dark hover:bg-primary-dark/90 text-white font-semibold text-sm shadow-sm transition duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ __('shop.clear_filters') }}
                            </a>
                        @endif
                        <a 
                            href="{{ r('products.index') }}" 
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-primary-gray/60 text-primary-dark hover:bg-primary-gray/30 font-semibold text-sm transition duration-200"
                        >
                            {{ __('shop.back_to_shop') }}
                        </a>
                    </div>
                </div>
            @else
                <!-- C) PRODUCT GRID -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-8 lg:gap-x-6">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
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
