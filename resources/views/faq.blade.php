<x-app-layout :title="__('seo.faq_title')" :description="__('seo.faq_description')">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
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
            @php($faqKeys = ['shipping', 'returns', 'tracking', 'payment', 'modify', 'support'])
            {{-- Rendered server-side; #faq-<key> in the URL opens that question (used by the footer links). --}}
            <div
                x-data="{ open: location.hash.startsWith('#faq-') ? location.hash.slice(5) : 'shipping' }"
                @hashchange.window="if (location.hash.startsWith('#faq-')) open = location.hash.slice(5)"
                class="bg-white rounded-3xl shadow-xl border border-primary-gray/50 overflow-hidden"
            >
                @foreach ($faqKeys as $key)
                    <div id="faq-{{ $key }}" class="border-b last:border-0 scroll-mt-24">
                        <button
                            type="button"
                            @click="open = open === '{{ $key }}' ? null : '{{ $key }}'"
                            :aria-expanded="(open === '{{ $key }}').toString()"
                            aria-controls="faq-{{ $key }}-answer"
                            class="w-full flex items-center justify-between gap-3 px-5 py-4 text-start hover:bg-primary-gray/40 transition duration-200"
                        >
                            <span class="text-sm font-semibold text-primary-dark flex-1">{{ policy_text("faq.items.$key.q") }}</span>
                            <svg :class="open === '{{ $key }}' ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="faq-{{ $key }}-answer" x-show="open === '{{ $key }}'" x-cloak x-transition.opacity.duration.200ms class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            {{ policy_text("faq.items.$key.a") }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- CTA -->
        <section>
            <div class="flex items-center justify-between bg-primary-gray/40 rounded-2xl p-6">
                <p class="text-primary-dark/80 text-sm">{{ __('faq.still_need_help') }}</p>
                <a href="{{ r('contact') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-dark text-white text-sm font-semibold hover:bg-primary-dark/90 transition">{{ __('faq.contact_us') }}</a>
            </div>
        </section>
    </div>
</x-app-layout>
