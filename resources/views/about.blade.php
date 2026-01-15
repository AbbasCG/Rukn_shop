<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16" style="font-family: 'Signika', sans-serif;" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <style>
            @media (prefers-reduced-motion: no-preference) {
                @keyframes fade-in-up { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
                .animate-fade-in-up { animation: fade-in-up 600ms ease-out both; }
            }
        </style>

        <!-- 1) Hero Section -->
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-12 text-center md:text-left animate-fade-in-up">
            <div class="space-y-4">
                <h1 class="text-4xl font-bold text-primary-dark">{{ __('about.title') }}</h1>
                <p class="text-neutral-700 text-base">{{ __('about.subtitle') }}</p>
                <p class="text-neutral-700">{{ __('about.description') }}</p>
            </div>
        </section>

        <!-- 2) Our Story -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start {{ app()->getLocale() === 'ar' ? 'rtl:grid-flow-dense' : '' }}">
            <!-- Left -->
            <div class="space-y-4 {{ app()->getLocale() === 'ar' ? 'md:col-start-2' : '' }}">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-gray/60 text-xs font-semibold text-primary-dark">{{ __('about.since') }}</div>
                <h2 class="text-xl font-semibold text-primary-dark">{{ __('about.story_title') }}</h2>
                <p class="text-neutral-700">{{ __('about.story_paragraph_1') }}</p>
                <p class="text-neutral-700">{{ __('about.story_paragraph_2') }}</p>
                <ul class="list-disc space-y-1 text-neutral-700 {{ app()->getLocale() === 'ar' ? 'pr-5' : 'pl-5' }}">
                    <li>{{ __('about.bullet_1') }}</li>
                    <li>{{ __('about.bullet_2') }}</li>
                    <li>{{ __('about.bullet_3') }}</li>
                </ul>
            </div>
            <!-- Right -->
            <div class="backdrop-blur-xl bg-white/70 shadow-xl rounded-3xl border border-white/20 p-8 {{ app()->getLocale() === 'ar' ? 'md:col-start-1 md:row-start-1' : '' }}">
                <div class="rounded-3xl bg-primary-gray/40 h-56 sm:h-72 flex items-center justify-center">
                    <svg class="w-16 h-16 text-primary-dark/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="mt-4 text-sm text-neutral-700">{{ __('about.experience_description') }}</p>
            </div>
        </section>

        <!-- 3) Mission & Values -->
        <section class="space-y-6">
            <h2 class="text-xl font-semibold text-primary-dark">{{ __('about.values_title') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Card -->
                <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-2xl transition-all">
                    <div class="h-10 w-10 rounded-xl bg-primary-gray/60 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-primary-dark">{{ __('about.value_fast_shipping_title') }}</h3>
                    <p class="text-xs text-neutral-700">{{ __('about.value_fast_shipping_desc') }}</p>
                </div>
                <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-2xl transition-all">
                    <div class="h-10 w-10 rounded-xl bg-primary-gray/60 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-primary-dark">{{ __('about.value_quality_title') }}</h3>
                    <p class="text-xs text-neutral-700">{{ __('about.value_quality_desc') }}</p>
                </div>
                <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-2xl transition-all">
                    <div class="h-10 w-10 rounded-xl bg-primary-gray/60 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.1.9-2 2-2h0a2 2 0 012 2v1h3a2 2 0 012 2v5a2 2 0 01-2 2H7a2 2 0 01-2-2v-5a2 2 0 012-2h3v-1a2 2 0 012-2h0z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-primary-dark">{{ __('about.value_secure_title') }}</h3>
                    <p class="text-xs text-neutral-700">{{ __('about.value_secure_desc') }}</p>
                </div>
                <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-2xl transition-all">
                    <div class="h-10 w-10 rounded-xl bg-primary-gray/60 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 13v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6m14-2a2 2 0 00-2-2h-2a4 4 0 00-8 0H6a2 2 0 00-2 2v0"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-primary-dark">{{ __('about.value_support_title') }}</h3>
                    <p class="text-xs text-neutral-700">{{ __('about.value_support_desc') }}</p>
                </div>
            </div>
        </section>

        <!-- 4) Partners / Trust -->
        <section class="space-y-6">
            <h2 class="text-xl font-semibold text-primary-dark">{{ __('about.partners_title') }}</h2>
            <div class="flex flex-wrap items-center gap-6 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                @foreach(["ALPHA","BETA","GAMMA","DELTA"] as $logo)
                    <div class="h-12 px-5 rounded-2xl bg-primary-gray/60 text-primary-dark/70 flex items-center justify-center opacity-60 hover:opacity-100 transition">{{ $logo }}</div>
                @endforeach
            </div>
        </section>

        <!-- 5) CTA -->
        <section>
            <div class="bg-primary-dark text-white rounded-3xl p-10 shadow-2xl">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                    <div>
                        <h3 class="text-xl font-semibold">{{ __('about.cta_title') }}</h3>
                        <p class="text-sm text-white/80">{{ __('about.cta_description') }}</p>
                    </div>
                    <div class="flex items-center gap-3 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-5 py-3 rounded-xl bg-white text-primary-dark text-sm font-semibold shadow-lg hover:bg-white/90 transition">{{ __('about.cta_shop_now') }}</a>
                        <a href="{{ route('contact') }}" class="inline-flex items-center px-5 py-3 rounded-xl border border-white text-white text-sm font-semibold hover:bg-white/10 transition">{{ __('about.cta_contact_us') }}</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>