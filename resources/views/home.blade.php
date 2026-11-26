<x-app-layout>
    <!-- 1. HERO SECTION -->
    <section class="min-h-[calc(100vh-80px)] bg-primary-light flex items-center">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                <!-- Left: Content -->
                <div class="flex flex-col justify-center py-12 text-start">
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">{{ __('home.hero_tagline') }}</p>
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-primary-dark mb-6 leading-tight">
                        {{ __('home.hero_title') }}
                    </h1>
                    <p class="text-lg text-primary-dark/70 mb-8 leading-relaxed max-w-lg">
                        {{ __('home.hero_subtitle') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ r('products.index') }}" class="btn-primary">
                            {{ __('home.shop_now') }}
                        </a>
                        <a href="{{ r('about') }}" class="btn-secondary">
                            {{ __('home.about_us_cta') }}
                        </a>
                    </div>
                </div>

                <!-- Right: Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96">
                    <div class="relative w-full h-full rounded-3xl overflow-hidden bg-primary-gray shadow-xl">
                        <img
                            src="{{ asset('images/banner.jpg') }}"
                            alt="{{ __('home.collection_hero_alt') }}"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-700 ease-in-out">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. NEW COLLECTION SECTION -->
    <section class="py-24 bg-primary-light">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-12">
                <div class="text-start">
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-3">{{ __('home.section_collection') }}</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark">{{ __('home.new_arrivals_title') }}</h2>
                    <p class="text-primary-dark/70 mt-3 max-w-xl">
                        {{ __('home.new_arrivals_subtitle') }}
                    </p>
                </div>
                <a href="{{ r('products.index') }}" class="nav-link-underline text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 hidden lg:inline-flex items-center gap-2">
                    <span>{{ __('home.view_all') }}</span>
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Products Grid -->
            @if($newArrivals->isNotEmpty())
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-8 lg:gap-x-6">
                @foreach($newArrivals as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-lg text-primary-dark/70">{{ __('home.featured_coming_soon') }}</p>
            </div>
            @endif
        </div>
    </section>

    @if($bestsellers->count() >= 4)
    <!-- 3. BESTSELLERS SECTION -->
    <section class="py-24 bg-primary-gray">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-3">{{ __('home.section_most_loved') }}</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark">{{ __('home.bestsellers_title') }}</h2>
                    <p class="text-primary-dark/70 mt-3 max-w-xl">
                        {{ __('home.bestsellers_subtitle') }}
                    </p>
                </div>
                <a href="{{ r('products.index') }}" class="nav-link-underline text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 hidden lg:inline-block">
                    {{ __('home.view_all') }} <span class="inline-block rtl:-scale-x-100" aria-hidden="true">→</span>
                </a>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-8 lg:gap-x-6">
                @foreach($bestsellers as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 4. FEATURES SECTION WITH LARGE IMAGE -->
    <section class="py-24 bg-primary-light">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Large Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96 lg:order-first order-last">
                    <div class="relative w-full h-full rounded-3xl overflow-hidden bg-primary-gray shadow-lg">
                        <img
                            src="{{ asset('images/banner.jpg') }}"
                            alt="{{ __('home.features_image_alt') }}"
                            class="w-full h-full object-cover">
                    </div>
                </div>

                <!-- Text + Features -->
                <div class="flex flex-col justify-center text-start">
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-6 leading-tight">
                        {{ __('home.features_title') }}
                    </h2>
                    <p class="text-primary-dark/70 mb-12 text-lg leading-relaxed">
                        {{ __('home.features_subtitle') }}
                    </p>

                    <!-- Features -->
                    <div class="space-y-8">
                        <!-- Feature 1 -->
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-primary-dark/10">
                                    <svg class="h-6 w-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-primary-dark mb-2 text-start">{{ __('home.feature_modest_title') }}</h3>
                                <p class="text-primary-dark/70 text-start">{{ __('home.feature_modest_desc') }}</p>
                            </div>
                        </div>

                        <!-- Feature 2 -->
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-primary-dark/10">
                                    <svg class="h-6 w-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-primary-dark mb-2 text-start">{{ __('home.feature_bakhoor_title') }}</h3>
                                <p class="text-primary-dark/70 text-start">{{ __('home.feature_bakhoor_desc') }}</p>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-primary-dark/10">
                                    <svg class="h-6 w-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-primary-dark mb-2 text-start">{{ __('home.feature_order_title') }}</h3>
                                <p class="text-primary-dark/70 text-start">{{ __('home.feature_order_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. BRAND STORY SECTION -->
    <section class="py-24 bg-primary-gray">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Text -->
                <div class="flex flex-col justify-center py-8">
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">{{ __('home.section_our_story') }}</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-6">
                        {{ __('home.story_title') }}
                    </h2>
                    <p class="text-primary-dark/70 mb-6 text-lg leading-relaxed">
                        {{ __('home.story_paragraph_1') }}
                    </p>
                    <p class="text-primary-dark/70 mb-8 text-lg leading-relaxed">
                        {{ __('home.story_paragraph_2') }}
                    </p>
                    <a href="{{ r('about') }}" class="nav-link-underline inline-flex items-center text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 w-fit">
                        {{ __('home.learn_more') }} <span class="inline-block rtl:-scale-x-100" aria-hidden="true">→</span>
                    </a>
                </div>

                <!-- Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96">
                    <div class="relative w-full h-full rounded-3xl overflow-hidden bg-primary-light shadow-lg">
                        <img
                            src="{{ asset('images/banner.jpg') }}"
                            alt="{{ __('home.story_image_alt') }}"
                            class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
