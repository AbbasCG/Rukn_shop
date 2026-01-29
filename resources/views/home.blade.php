<x-app-layout>
    <!-- 1. HERO SECTION -->
    <section class="min-h-[calc(100vh-80px)] bg-primary-light flex items-center" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                <!-- Left: Content -->
                <div class="flex flex-col justify-center py-12 {{ app()->getLocale() === 'ar' ? 'lg:order-last' : '' }}">
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">{{ __('home.hero_tagline') }}</p>
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-primary-dark mb-6 leading-tight">
                        {{ __('home.hero_title') }}
                    </h1>
                    <p class="text-lg text-primary-dark/70 mb-8 leading-relaxed max-w-lg">
                        {{ __('home.hero_subtitle') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                        <a href="{{ route('products.index') }}" class="nav-link-underline inline-flex items-center justify-center px-8 py-3 bg-primary-dark text-primary-light font-semibold hover:bg-primary-dark/90 hover:scale-105 transition-all duration-300 ease-in-out">
                            {{ __('home.shop_now') }}
                        </a>
                        <a href="{{ route('about') }}" class="nav-link-underline inline-flex items-center justify-center px-8 py-3 border-2 border-primary-dark text-primary-dark font-semibold hover:bg-primary-dark/5 hover:scale-105 transition-all duration-300 ease-in-out">
                            {{ __('home.view_collection') }}
                        </a>
                    </div>
                </div>

                <!-- Right: Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96 {{ app()->getLocale() === 'ar' ? 'lg:order-first' : '' }}">
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
    <section class="py-24 bg-primary-light" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-12 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                <div>
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-3">{{ __('home.section_collection') }}</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark">{{ __('home.new_arrivals_title') }}</h2>
                    <p class="text-primary-dark/70 mt-3 max-w-xl">
                        {{ __('home.new_arrivals_subtitle') }}
                    </p>
                </div>
                <a href="{{ route('products.index') }}" class="nav-link-underline text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 hidden lg:inline-block">
                    {{ __('home.view_all') }} →
                </a>
            </div>

            <!-- Products Grid -->
            @if($featuredProducts && $featuredProducts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($featuredProducts->take(4) as $product)
                <div class="group flex flex-col h-full">
                    <!-- Product Image -->
                    <div class="relative h-80 bg-primary-gray rounded-2xl overflow-hidden mb-6 flex-shrink-0">
                        @if($product->primaryImage())
                        <img
                            src="{{ asset($product->primaryImage()->path) }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-20 h-20 text-primary-dark/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <h3 class="text-lg font-semibold text-primary-dark mb-2 line-clamp-2">
                        {{ $product->name }}
                    </h3>

                    @if($product->description)
                    <p class="text-sm text-primary-dark/60 mb-4 line-clamp-2">
                        {{ $product->description }}
                    </p>
                    @endif

                    <div class="mt-auto">
                        <p class="text-lg font-semibold text-primary-dark mb-4">
                            €{{ number_format($product->price, 2) }}
                        </p>

                        <a
                            href="{{ route('products.show', $product) }}"
                            class="nav-link-underline w-full inline-flex items-center justify-center px-4 py-3 border-2 border-primary-dark text-primary-dark font-semibold hover:bg-primary-dark hover:text-primary-light transition-all duration-300 ease-in-out rounded-lg">
                            {{ __('home.view_details') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-lg text-primary-dark/70">{{ __('home.featured_coming_soon') }}</p>
            </div>
            @endif
        </div>
    </section>

    <!-- 3. BESTSELLERS SECTION -->
    <section class="py-24 bg-primary-gray" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-12 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                <div>
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-3">{{ __('home.section_most_loved') }}</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark">{{ __('home.bestsellers_title') }}</h2>
                    <p class="text-primary-dark/70 mt-3 max-w-xl">
                        {{ __('home.bestsellers_subtitle') }}
                    </p>
                </div>
                <a href="{{ route('products.index') }}" class="nav-link-underline text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 hidden lg:inline-block">
                    {{ __('home.view_all') }} →
                </a>
            </div>

            <!-- Products Grid -->
            @if($featuredProducts && $featuredProducts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($featuredProducts->take(4) as $product)
                <div class="group flex flex-col h-full">
                    <!-- Product Image -->
                    <div class="relative h-80 bg-primary-light rounded-2xl overflow-hidden mb-6 flex-shrink-0">
                        @if($product->primaryImage())
                        <img
                            src="{{ asset($product->primaryImage()->path) }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-20 h-20 text-primary-dark/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <h3 class="text-lg font-semibold text-primary-dark mb-2 line-clamp-2">
                        {{ $product->name }}
                    </h3>

                    @if($product->description)
                    <p class="text-sm text-primary-dark/60 mb-4 line-clamp-2">
                        {{ $product->description }}
                    </p>
                    @endif

                    <div class="mt-auto">
                        <p class="text-lg font-semibold text-primary-dark mb-4">
                            €{{ number_format($product->price, 2) }}
                        </p>

                        <a
                            href="{{ route('products.show', $product) }}"
                            class="nav-link-underline w-full inline-flex items-center justify-center px-4 py-3 border-2 border-primary-dark text-primary-dark font-semibold hover:bg-primary-dark hover:text-primary-light transition-all duration-300 ease-in-out rounded-lg">
                            {{ __('home.view_details') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-lg text-primary-dark/70">{{ __('home.bestsellers_coming_soon') }}</p>
            </div>
            @endif
        </div>
    </section>

    <!-- 4. FEATURES SECTION WITH LARGE IMAGE -->
    <section class="py-24 bg-primary-light" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Large Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96 {{ app()->getLocale() === 'ar' ? 'lg:order-last' : 'lg:order-first' }} order-last">
                    <div class="relative w-full h-full rounded-3xl overflow-hidden bg-primary-gray shadow-lg">
                        <img
                            src="{{ asset('images/banner.jpg') }}"
                            alt="{{ __('home.premium_collection_alt') }}"
                            class="w-full h-full object-cover">
                    </div>
                </div>

                <!-- Text + Features -->
                <div class="flex flex-col justify-center">
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-6 leading-tight">
                        {{ __('home.tradition_meets_modern_title') }}
                    </h2>
                    <p class="text-primary-dark/70 mb-12 text-lg leading-relaxed">
                        {{ __('home.tradition_meets_modern_subtitle') }}
                    </p>

                    <!-- Features -->
                    <div class="space-y-8">
                        <!-- Feature 1 -->
                        <div class="flex gap-4 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-primary-dark/10">
                                    <svg class="h-6 w-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-primary-dark mb-2">{{ __('home.feature_premium_fabrics_title') }}</h3>
                                <p class="text-primary-dark/70">{{ __('home.feature_premium_fabrics_desc') }}</p>
                            </div>
                        </div>

                        <!-- Feature 2 -->
                        <div class="flex gap-4 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-primary-dark/10">
                                    <svg class="h-6 w-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-primary-dark mb-2">{{ __('home.feature_craftsmanship_title') }}</h3>
                                <p class="text-primary-dark/70">{{ __('home.feature_craftsmanship_desc') }}</p>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="flex gap-4 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-primary-dark/10">
                                    <svg class="h-6 w-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-primary-dark mb-2">{{ __('home.feature_timeless_design_title') }}</h3>
                                <p class="text-primary-dark/70">{{ __('home.feature_timeless_design_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. BRAND STORY SECTION -->
    <section class="py-24 bg-primary-gray" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Text -->
                <div class="flex flex-col justify-center py-8 {{ app()->getLocale() === 'ar' ? 'lg:order-last' : '' }}">
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
                    <a href="{{ route('about') }}" class="nav-link-underline inline-flex items-center text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 w-fit">
                        {{ __('home.learn_more') }} →
                    </a>
                </div>

                <!-- Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96 {{ app()->getLocale() === 'ar' ? 'lg:order-first' : '' }}">
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

    <!-- 6. TESTIMONIALS SECTION -->
    <section class="py-24 bg-primary-light" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">{{ __('home.section_reviews') }}</p>
                <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-4">
                    {{ __('home.testimonials_title') }}
                </h2>
                <p class="text-primary-dark/70 text-lg">
                    {{ __('home.testimonials_subtitle') }}
                </p>
            </div>

            <!-- Testimonials Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="flex gap-1 mb-4 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-primary-dark fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            @endfor
                    </div>
                    <p class="text-primary-dark/80 mb-6 leading-relaxed">
                        {{ __('home.testimonial_1_text') }}
                    </p>
                    <p class="font-semibold text-primary-dark">{{ __('home.testimonial_1_author') }}</p>
                    <p class="text-sm text-primary-dark/60">{{ __('home.testimonial_1_location') }}</p>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="flex gap-1 mb-4 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-primary-dark fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            @endfor
                    </div>
                    <p class="text-primary-dark/80 mb-6 leading-relaxed">
                        {{ __('home.testimonial_2_text') }}
                    </p>
                    <p class="font-semibold text-primary-dark">{{ __('home.testimonial_2_author') }}</p>
                    <p class="text-sm text-primary-dark/60">{{ __('home.testimonial_2_location') }}</p>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="flex gap-1 mb-4 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-primary-dark fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            @endfor
                    </div>
                    <p class="text-primary-dark/80 mb-6 leading-relaxed">
                        {{ __('home.testimonial_3_text') }}
                    </p>
                    <p class="font-semibold text-primary-dark">{{ __('home.testimonial_3_author') }}</p>
                    <p class="text-sm text-primary-dark/60">{{ __('home.testimonial_3_location') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. INSTAGRAM SECTION -->
    <section class="py-24 bg-primary-gray" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">{{ __('home.section_follow_us') }}</p>
                <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-4">
                    {{ __('home.instagram_title') }}
                </h2>
                <p class="text-primary-dark/70 text-lg">
                    {{ __('home.instagram_subtitle') }}
                </p>
            </div>

            <!-- Instagram Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @for($i = 0; $i < 6; $i++)
                    <a href="#" class="relative h-64 md:h-80 bg-primary-light rounded-2xl overflow-hidden group cursor-pointer">
                    <div class="absolute inset-0 bg-primary-dark/0 group-hover:bg-primary-dark/40 transition-colors duration-300 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                        </svg>
                    </div>
                    <div class="w-full h-full bg-primary-light flex items-center justify-center">
                        <svg class="w-12 h-12 text-primary-dark/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    </a>
                    @endfor
            </div>

            <!-- Slider Dots -->
            <div class="flex justify-center gap-2 mt-8">
                <button class="w-2 h-2 rounded-full bg-primary-dark transition-all duration-300"></button>
                <button class="w-2 h-2 rounded-full bg-primary-dark/30 hover:bg-primary-dark/60 transition-all duration-300"></button>
            </div>
        </div>
    </section>


</x-app-layout>