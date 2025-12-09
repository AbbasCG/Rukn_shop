<x-app-layout>
    <!-- 1. HERO SECTION -->
    <section class="min-h-[calc(100vh-80px)] bg-primary-light flex items-center">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                <!-- Left: Content -->
                <div class="flex flex-col justify-center py-12">
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">Elegance woven into every moment</p>
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-primary-dark mb-6 leading-tight">
                        Discover the Art of Living
                    </h1>
                    <p class="text-lg text-primary-dark/70 mb-8 leading-relaxed max-w-lg">
                        Experience our curated collection of timeless pieces designed for those who appreciate quality, craftsmanship, and understated elegance.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('products.index') }}" class="nav-link-underline inline-flex items-center justify-center px-8 py-3 bg-primary-dark text-primary-light font-semibold hover:bg-primary-dark/90 hover:scale-105 transition-all duration-300 ease-in-out">
                            Shop Now
                        </a>
                        <a href="{{ route('about') }}" class="nav-link-underline inline-flex items-center justify-center px-8 py-3 border-2 border-primary-dark text-primary-dark font-semibold hover:bg-primary-dark/5 hover:scale-105 transition-all duration-300 ease-in-out">
                            View Collection
                        </a>
                    </div>
                </div>

                <!-- Right: Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96">
                    <div class="relative w-full h-full rounded-3xl overflow-hidden bg-primary-gray shadow-xl">
                        <img 
                            src="{{ asset('storage/images/banner.jpg') }}" 
                            alt="Collection Hero" 
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-700 ease-in-out"
                        >
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. NEW COLLECTION SECTION -->
    <section class="py-24 bg-primary-light">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-3">Collection</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark">New Arrivals</h2>
                    <p class="text-primary-dark/70 mt-3 max-w-xl">
                        Curate your space with our latest seasonal collection, handpicked for modern elegance.
                    </p>
                </div>
                <a href="{{ route('products.index') }}" class="nav-link-underline text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 hidden lg:inline-block">
                    View all →
                </a>
            </div>

            <!-- Products Grid -->
            @if($featuredProducts && $featuredProducts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($featuredProducts->take(4) as $product)
                        <div class="group flex flex-col h-full">
                            <!-- Product Image -->
                            <div class="relative h-80 bg-primary-gray rounded-2xl overflow-hidden mb-6 flex-shrink-0">
                                @if($product->image)
                                    <img 
                                        src="{{ $product->image }}" 
                                        alt="{{ $product->name }}" 
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out"
                                    >
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
                                    ${{ number_format($product->price, 2) }}
                                </p>

                                <a 
                                    href="{{ route('products.show', $product) }}" 
                                    class="nav-link-underline w-full inline-flex items-center justify-center px-4 py-3 border-2 border-primary-dark text-primary-dark font-semibold hover:bg-primary-dark hover:text-primary-light transition-all duration-300 ease-in-out rounded-lg"
                                >
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-lg text-primary-dark/70">Featured collection coming soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- 3. BESTSELLERS SECTION -->
    <section class="py-24 bg-primary-gray">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-3">Most Loved</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark">Bestsellers</h2>
                    <p class="text-primary-dark/70 mt-3 max-w-xl">
                        Discover what customers love most. Timeless pieces with exceptional craftsmanship.
                    </p>
                </div>
                <a href="{{ route('products.index') }}" class="nav-link-underline text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 hidden lg:inline-block">
                    View all →
                </a>
            </div>

            <!-- Products Grid -->
            @if($featuredProducts && $featuredProducts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($featuredProducts->take(4) as $product)
                        <div class="group flex flex-col h-full">
                            <!-- Product Image -->
                            <div class="relative h-80 bg-primary-light rounded-2xl overflow-hidden mb-6 flex-shrink-0">
                                @if($product->image)
                                    <img 
                                        src="{{ $product->image }}" 
                                        alt="{{ $product->name }}" 
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out"
                                    >
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
                                    ${{ number_format($product->price, 2) }}
                                </p>

                                <a 
                                    href="{{ route('products.show', $product) }}" 
                                    class="nav-link-underline w-full inline-flex items-center justify-center px-4 py-3 border-2 border-primary-dark text-primary-dark font-semibold hover:bg-primary-dark hover:text-primary-light transition-all duration-300 ease-in-out rounded-lg"
                                >
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-lg text-primary-dark/70">Bestsellers coming soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- 4. FEATURES SECTION WITH LARGE IMAGE -->
    <section class="py-24 bg-primary-light">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Large Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96 order-last lg:order-first">
                    <div class="relative w-full h-full rounded-3xl overflow-hidden bg-primary-gray shadow-lg">
                        <img 
                            src="{{ asset('storage/images/banner.jpg') }}" 
                            alt="Premium Collection" 
                            class="w-full h-full object-cover"
                        >
                    </div>
                </div>

                <!-- Text + Features -->
                <div class="flex flex-col justify-center">
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-6 leading-tight">
                        Where tradition meets modern living
                    </h2>
                    <p class="text-primary-dark/70 mb-12 text-lg leading-relaxed">
                        Every piece in our collection tells a story of craftsmanship, quality, and timeless design. 
                        We believe that true elegance lies in simplicity and authenticity.
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
                                <h3 class="text-lg font-semibold text-primary-dark mb-2">Premium Fabrics</h3>
                                <p class="text-primary-dark/70">Carefully sourced materials from the finest suppliers worldwide.</p>
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
                                <h3 class="text-lg font-semibold text-primary-dark mb-2">Expert Craftsmanship</h3>
                                <p class="text-primary-dark/70">Each piece is meticulously crafted by skilled artisans.</p>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-primary-dark/10">
                                    <svg class="h-6 w-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-primary-dark mb-2">Timeless Design</h3>
                                <p class="text-primary-dark/70">Collections designed to transcend trends and last a lifetime.</p>
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
                    <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">Our Story</p>
                    <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-6">
                        The Art of Elegance
                    </h2>
                    <p class="text-primary-dark/70 mb-6 text-lg leading-relaxed">
                        Founded on the principle that quality should never be compromised, Rukn Shop brings together 
                        the best of traditional craftsmanship and contemporary design.
                    </p>
                    <p class="text-primary-dark/70 mb-8 text-lg leading-relaxed">
                        We carefully curate every collection to ensure that each piece reflects our commitment to 
                        elegance, sustainability, and timeless beauty.
                    </p>
                    <a href="{{ route('about') }}" class="nav-link-underline inline-flex items-center text-primary-dark font-semibold hover:text-primary-dark/70 transition-colors duration-300 w-fit">
                        Learn more about us →
                    </a>
                </div>

                <!-- Image -->
                <div class="relative h-96 lg:h-full lg:min-h-96">
                    <div class="relative w-full h-full rounded-3xl overflow-hidden bg-primary-light shadow-lg">
                        <img 
                            src="{{ asset('storage/images/banner.jpg') }}" 
                            alt="Rukn Shop Story" 
                            class="w-full h-full object-cover"
                        >
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. TESTIMONIALS SECTION -->
    <section class="py-24 bg-primary-light">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">Reviews</p>
                <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-4">
                    Voices of Elegance
                </h2>
                <p class="text-primary-dark/70 text-lg">
                    What our customers say about their experience with Rukn Shop
                </p>
            </div>

            <!-- Testimonials Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="flex gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-primary-dark fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                        @endfor
                    </div>
                    <p class="text-primary-dark/80 mb-6 leading-relaxed">
                        "The quality is exceptional. I've never felt anything quite like it. Definitely worth every penny and I've already ordered twice more!"
                    </p>
                    <p class="font-semibold text-primary-dark">Sarah Anderson</p>
                    <p class="text-sm text-primary-dark/60">New York, USA</p>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="flex gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-primary-dark fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                        @endfor
                    </div>
                    <p class="text-primary-dark/80 mb-6 leading-relaxed">
                        "I'm impressed by the attention to detail. It's clear that these pieces are made with passion and expertise. Highly recommended!"
                    </p>
                    <p class="font-semibold text-primary-dark">James Mitchell</p>
                    <p class="text-sm text-primary-dark/60">London, UK</p>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="flex gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-primary-dark fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                        @endfor
                    </div>
                    <p class="text-primary-dark/80 mb-6 leading-relaxed">
                        "What I love about Rukn Shop is the consistency of quality. Every piece feels special and lasts for years."
                    </p>
                    <p class="font-semibold text-primary-dark">Emma Rodriguez</p>
                    <p class="text-sm text-primary-dark/60">Barcelona, Spain</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. INSTAGRAM SECTION -->
    <section class="py-24 bg-primary-gray">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <p class="text-sm font-semibold text-primary-dark/60 uppercase tracking-widest mb-4">Follow Us</p>
                <h2 class="text-4xl md:text-5xl font-bold text-primary-dark mb-4">
                    On Instagram
                </h2>
                <p class="text-primary-dark/70 text-lg">
                    @ruknshop • Join our community and share your elegance
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
                            <svg class="w-12 h-12 text-primary-dark/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
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