<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16" style="font-family: 'Signika', sans-serif;">
        <style>
            @media (prefers-reduced-motion: no-preference) {
                @keyframes fade-in-up { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
                .animate-fade-in-up { animation: fade-in-up 600ms ease-out both; }
            }
        </style>

        <!-- 1) Hero Section -->
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-12 text-center md:text-left animate-fade-in-up">
            <div class="space-y-4">
                <h1 class="text-4xl font-bold text-primary-dark">About Rukn Shop</h1>
                <p class="text-neutral-700 text-base">Your trusted online store for quality products, fair prices, and friendly service.</p>
                <p class="text-neutral-700">We curate products we love and stand behind, combining fast shipping, clear communication, and support you can count on — so shopping feels easy and enjoyable.</p>
            </div>
        </section>

        <!-- 2) Our Story -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
            <!-- Left -->
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-gray/60 text-xs font-semibold text-primary-dark">Since 2024</div>
                <h2 class="text-xl font-semibold text-primary-dark">Our Story</h2>
                <p class="text-neutral-700">Rukn Shop started with a simple idea: build a store we'd love to shop at ourselves. We’ve grown by listening to customers and refining every step — from selection to delivery.</p>
                <p class="text-neutral-700">What makes us different is our commitment to honest quality, transparent pricing, and responsive support. We believe small details make a big difference.</p>
                <ul class="list-disc pl-5 space-y-1 text-neutral-700">
                    <li>Curated catalog with everyday value</li>
                    <li>Fast, reliable shipping</li>
                    <li>Helpful support from real people</li>
                </ul>
            </div>
            <!-- Right -->
            <div class="backdrop-blur-xl bg-white/70 shadow-xl rounded-3xl border border-white/20 p-8">
                <div class="rounded-3xl bg-primary-gray/40 h-56 sm:h-72 flex items-center justify-center">
                    <svg class="w-16 h-16 text-primary-dark/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="mt-4 text-sm text-neutral-700">A clean, modern experience that keeps you focused on finding what you need — quickly.</p>
            </div>
        </section>

        <!-- 3) Mission & Values -->
        <section class="space-y-6">
            <h2 class="text-xl font-semibold text-primary-dark">Our Values</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Card -->
                <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-2xl transition-all">
                    <div class="h-10 w-10 rounded-xl bg-primary-gray/60 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-primary-dark">Fast Shipping</h3>
                    <p class="text-xs text-neutral-700">We dispatch orders quickly and keep you updated along the way.</p>
                </div>
                <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-2xl transition-all">
                    <div class="h-10 w-10 rounded-xl bg-primary-gray/60 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-primary-dark">Quality Products</h3>
                    <p class="text-xs text-neutral-700">We select items for durability, value, and genuine usefulness.</p>
                </div>
                <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-2xl transition-all">
                    <div class="h-10 w-10 rounded-xl bg-primary-gray/60 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.1.9-2 2-2h0a2 2 0 012 2v1h3a2 2 0 012 2v5a2 2 0 01-2 2H7a2 2 0 01-2-2v-5a2 2 0 012-2h3v-1a2 2 0 012-2h0z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-primary-dark">Secure Payments</h3>
                    <p class="text-xs text-neutral-700">Shop confidently with trusted, secure payment methods.</p>
                </div>
                <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-2xl transition-all">
                    <div class="h-10 w-10 rounded-xl bg-primary-gray/60 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 13v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6m14-2a2 2 0 00-2-2h-2a4 4 0 00-8 0H6a2 2 0 00-2 2v0"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-primary-dark">Friendly Support</h3>
                    <p class="text-xs text-neutral-700">Real people ready to help with orders, returns, and advice.</p>
                </div>
            </div>
        </section>

        <!-- 4) Partners / Trust -->
        <section class="space-y-6">
            <h2 class="text-xl font-semibold text-primary-dark">Trusted by partners</h2>
            <div class="flex flex-wrap items-center gap-6">
                @foreach(["ALPHA","BETA","GAMMA","DELTA"] as $logo)
                    <div class="h-12 px-5 rounded-2xl bg-primary-gray/60 text-primary-dark/70 flex items-center justify-center opacity-60 hover:opacity-100 transition">{{ $logo }}</div>
                @endforeach
            </div>
        </section>

        <!-- 5) CTA -->
        <section>
            <div class="bg-primary-dark text-white rounded-3xl p-10 shadow-2xl">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-semibold">Ready to explore Rukn Shop?</h3>
                        <p class="text-sm text-white/80">Browse our latest arrivals or get in touch — we’re here to help.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-5 py-3 rounded-xl bg-white text-primary-dark text-sm font-semibold shadow-lg hover:bg-white/90 transition">Shop Now</a>
                        <a href="{{ route('contact') }}" class="inline-flex items-center px-5 py-3 rounded-xl border border-white text-white text-sm font-semibold hover:bg-white/10 transition">Contact Us</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>