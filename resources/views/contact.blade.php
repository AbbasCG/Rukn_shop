<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16" style="font-family: 'Signika', sans-serif;" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <style>
            @media (prefers-reduced-motion: no-preference) {
                @keyframes fade-in-up { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
                .animate-fade-in-up { animation: fade-in-up 600ms ease-out both; }
            }
        </style>

        <!-- 1) Hero / Intro -->
         <!-- TEST COMMIT -->
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-12 text-center md:text-left animate-fade-in-up">
            <div class="space-y-4">
                <h1 class="text-4xl font-bold text-primary-dark">{{ __('contact.title') }}</h1>
                <p class="text-neutral-700">{{ __('contact.subtitle') }}</p>
            </div>
        </section>

        <!-- 2) Two-Column Layout -->
        <section>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 {{ app()->getLocale() === 'ar' ? 'rtl:grid-flow-dense' : '' }}">
                <!-- LEFT (Form) -->
                <div class="lg:col-span-2 backdrop-blur-xl bg-white/70 shadow-xl rounded-3xl border border-white/20 p-8 space-y-6 {{ app()->getLocale() === 'ar' ? 'lg:col-start-2' : '' }}">
                    <form method="POST" action="#" onsubmit="return false;">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('contact.form.name') }}</label>
                                <input id="name" type="text" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" placeholder="{{ __('contact.form.name_placeholder') }}" />
                            </div>
                            <div>
                                <label for="email" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('contact.form.email') }}</label>
                                <input id="email" type="email" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" placeholder="{{ __('contact.form.email_placeholder') }}" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="subject" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('contact.form.subject') }}</label>
                                <input id="subject" type="text" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" placeholder="{{ __('contact.form.subject_placeholder') }}" />
                            </div>
                            <div>
                                <label for="message" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('contact.form.message') }}</label>
                                <textarea id="message" rows="6" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" placeholder="{{ __('contact.form.message_placeholder') }}"></textarea>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full md:w-auto inline-flex items-center px-6 py-3 rounded-xl bg-primary-dark text-white font-semibold shadow-lg hover:bg-primary-dark/90 transition">{{ __('contact.form.submit') }}</button>
                        </div>
                    </form>
                </div>

                <!-- RIGHT (Info Card) -->
                <div class="bg-white shadow-xl rounded-3xl p-8 space-y-6 {{ app()->getLocale() === 'ar' ? 'lg:col-start-1 lg:row-start-1' : '' }}">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 text-primary-dark/80 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <div>
                                <p class="text-sm font-semibold">{{ __('contact.info.email_label') }}</p>
                                <p class="text-xs">{{ __('contact.info.email_value') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-primary-dark/80 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m0 4v10m6-10h6m-6 0v10"/></svg>
                            <div>
                                <p class="text-sm font-semibold">{{ __('contact.info.phone_label') }}</p>
                                <p class="text-xs">{{ __('contact.info.phone_value') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-primary-dark/80 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12m0 0l4.243-4.243M13.414 12H3"/></svg>
                            <div>
                                <p class="text-sm font-semibold">{{ __('contact.info.address_label') }}</p>
                                <p class="text-xs">{{ __('contact.info.address_value') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-primary-dark/80 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"/></svg>
                            <div>
                                <p class="text-sm font-semibold">{{ __('contact.info.opening_hours_label') }}</p>
                                <p class="text-xs">{{ __('contact.info.opening_hours_value') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        

            <!-- 4) FAQ Section -->
            <section id="faq" class="space-y-6">
                <div class="space-y-2">
                    <h2 class="text-2xl md:text-3xl font-bold text-primary-dark">{{ __('contact.faq.title') }}</h2>
                    <p class="text-sm text-primary-dark/70">{{ __('contact.faq.subtitle') }}</p>
                </div>

                <div x-data="{ open: null }" class="bg-white rounded-3xl shadow-xl border border-primary-gray/50 overflow-hidden">
                    <!-- Item 1 -->
                    <div class="border-b last:border-0">
                        <button type="button" @click="open === 1 ? open = null : open = 1" class="w-full flex items-center justify-between px-5 py-4 hover:bg-primary-gray/40 transition duration-200 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <span class="text-sm font-semibold text-primary-dark">{{ __('contact.faq.question_1') }}</span>
                            <svg :class="open === 1 ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === 1" x-transition.opacity.duration.200ms x-collapse class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            {{ __('contact.faq.answer_1') }}
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="border-b last:border-0">
                        <button type="button" @click="open === 2 ? open = null : open = 2" class="w-full flex items-center justify-between px-5 py-4 hover:bg-primary-gray/40 transition duration-200 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <span class="text-sm font-semibold text-primary-dark">{{ __('contact.faq.question_2') }}</span>
                            <svg :class="open === 2 ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === 2" x-transition.opacity.duration.200ms x-collapse class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            {{ __('contact.faq.answer_2') }}
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="border-b last:border-0">
                        <button type="button" @click="open === 3 ? open = null : open = 3" class="w-full flex items-center justify-between px-5 py-4 hover:bg-primary-gray/40 transition duration-200 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <span class="text-sm font-semibold text-primary-dark">{{ __('contact.faq.question_3') }}</span>
                            <svg :class="open === 3 ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === 3" x-transition.opacity.duration.200ms x-collapse class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            {{ __('contact.faq.answer_3') }}
                        </div>
                    </div>

                    <!-- Item 4 -->
                    <div class="border-b last:border-0">
                        <button type="button" @click="open === 4 ? open = null : open = 4" class="w-full flex items-center justify-between px-5 py-4 hover:bg-primary-gray/40 transition duration-200 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <span class="text-sm font-semibold text-primary-dark">{{ __('contact.faq.question_4') }}</span>
                            <svg :class="open === 4 ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === 4" x-transition.opacity.duration.200ms x-collapse class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            {{ __('contact.faq.answer_4') }}
                        </div>
                    </div>

                    <!-- Item 5 -->
                    <div class="border-b last:border-0">
                        <button type="button" @click="open === 5 ? open = null : open = 5" class="w-full flex items-center justify-between px-5 py-4 hover:bg-primary-gray/40 transition duration-200 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <span class="text-sm font-semibold text-primary-dark">{{ __('contact.faq.question_5') }}</span>
                            <svg :class="open === 5 ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === 5" x-transition.opacity.duration.200ms x-collapse class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            {{ __('contact.faq.answer_5') }}
                        </div>
                    </div>

                    <!-- Item 6 -->
                    <div class="border-b last:border-0">
                        <button type="button" @click="open === 6 ? open = null : open = 6" class="w-full flex items-center justify-between px-5 py-4 hover:bg-primary-gray/40 transition duration-200 {{ app()->getLocale() === 'ar' ? 'rtl:flex-row-reverse' : '' }}">
                            <span class="text-sm font-semibold text-primary-dark">{{ __('contact.faq.question_6') }}</span>
                            <svg :class="open === 6 ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === 6" x-transition.opacity.duration.200ms x-collapse class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            {{ __('contact.faq.answer_6') }}
                        </div>
                    </div>
                </div>
            </section>
    </div>
</x-app-layout>