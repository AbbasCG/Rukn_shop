<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12" style="font-family: 'Signika', sans-serif;">
        <style>
            @media (prefers-reduced-motion: no-preference) {
                @keyframes fade-in-up {
                    from {
                        opacity: 0;
                        transform: translateY(12px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .animate-fade-in-up {
                    animation: fade-in-up 600ms ease-out both;
                }
            }
        </style>
        <!-- Intro Header -->
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-10">
            <h1 class="text-4xl font-bold text-primary-dark">{{ __('faq.title') }}</h1>
            <p class="mt-2 text-primary-dark/70">{{ __('faq.subtitle') }}</p>
        </section>

        <!-- FAQ Accordion -->
        <section>
            <div x-data="{ open: 1 }" class="bg-white rounded-3xl shadow-xl border border-primary-gray/50 overflow-hidden">
                <!-- Repeat same items as contact for consistency -->
                <template x-for="i in 6">
                    <div class="border-b last:border-0">
                        <button type="button" @click="open === i ? open = null : open = i" class="w-full flex items-center justify-between px-5 py-4 hover:bg-primary-gray/40 transition duration-200">
                            <span class="text-sm font-semibold text-primary-dark" x-text="[
                                '{{ __('faq.items.shipping.q') }}',
                                '{{ __('faq.items.returns.q') }}',
                                '{{ __('faq.items.tracking.q') }}',
                                '{{ __('faq.items.payment.q') }}',
                                '{{ __('faq.items.modify.q') }}',
                                '{{ __('faq.items.support.q') }}'
                            ][i-1]"></span>
                            <svg :class="open === i ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open === i" x-transition.opacity.duration.200ms x-collapse class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            <span x-text="[
                                '{{ __('faq.items.shipping.a') }}',
                                '{{ __('faq.items.returns.a') }}',
                                '{{ __('faq.items.tracking.a') }}',
                                '{{ __('faq.items.payment.a') }}',
                                '{{ __('faq.items.modify.a') }}',
                                '{{ __('faq.items.support.a') }}'
                            ][i-1]"></span>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- CTA -->
        <section>
            <div class="flex items-center justify-between bg-primary-gray/40 rounded-2xl p-6">
                <p class="text-primary-dark/80 text-sm">{{ __('faq.still_need_help') }}</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-dark text-white text-sm font-semibold hover:bg-primary-dark/90 transition">{{ __('faq.contact_us') }}</a>
            </div>
        </section>
    </div>
</x-app-layout>