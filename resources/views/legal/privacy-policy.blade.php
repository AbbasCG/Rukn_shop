<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8" style="font-family: 'Signika', sans-serif;">
        <!-- Header -->
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-10 {{ app()->getLocale() === 'ar' ? 'text-right' : '' }}">
            <h1 class="text-4xl font-bold text-primary-dark">{{ __('legal.privacy.title') }}</h1>
            <p class="mt-2 text-sm text-primary-dark/70">{{ __('legal.privacy.last_updated') }}: {{ date('F j, Y') }}</p>
        </section>

        <!-- Content -->
        <section class="bg-white rounded-3xl shadow-xl border border-primary-gray/50 p-8 space-y-6 {{ app()->getLocale() === 'ar' ? 'text-right' : '' }}">
            <!-- Introduction -->
            <div class="pb-6 border-b border-primary-gray/30">
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.privacy.intro') }}</p>
            </div>

            <!-- Section 1 -->
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.privacy.section_1_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.privacy.section_1_content') }}</p>
            </div>

            <!-- Section 2 -->
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.privacy.section_2_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.privacy.section_2_content') }}</p>
            </div>

            <!-- Section 3 -->
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.privacy.section_3_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.privacy.section_3_content') }}</p>
            </div>

            <!-- Section 4 -->
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.privacy.section_4_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.privacy.section_4_content') }}</p>
            </div>

            <!-- Section 5 -->
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.privacy.section_5_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.privacy.section_5_content') }}</p>
            </div>

            <!-- Section 6 -->
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.privacy.section_6_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.privacy.section_6_content') }}</p>
            </div>

            <!-- Section 7 -->
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.privacy.section_7_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.privacy.section_7_content') }}</p>
            </div>
        </section>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-primary-dark hover:text-primary-dark/70 transition-colors duration-300">
                <svg class="w-5 h-5 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>{{ __('Back to Home') }}</span>
            </a>
        </div>
    </div>
</x-app-layout>
