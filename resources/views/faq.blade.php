<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12" style="font-family: 'Signika', sans-serif;">
        <!-- Intro Header -->
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-10">
            <h1 class="text-4xl font-bold text-primary-dark">FAQ</h1>
            <p class="mt-2 text-primary-dark/70">Quick answers to common questions.</p>
        </section>

        <!-- FAQ Accordion -->
        <section>
            <div x-data="{ open: 1 }" class="bg-white rounded-3xl shadow-xl border border-primary-gray/50 overflow-hidden">
                <!-- Repeat same items as contact for consistency -->
                <template x-for="i in 6">
                    <div class="border-b last:border-0">
                        <button type="button" @click="open === i ? open = null : open = i" class="w-full flex items-center justify-between px-5 py-4 hover:bg-primary-gray/40 transition duration-200">
                            <span class="text-sm font-semibold text-primary-dark" x-text="[
                                'How long does shipping take?',
                                'Can I return a product?',
                                'How can I track my order?',
                                'What payment methods do you accept?',
                                'Can I change or cancel my order?',
                                'How do I contact support?'
                            ][i-1]"></span>
                            <svg :class="open === i ? 'rotate-180' : ''" class="w-5 h-5 text-primary-dark transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === i" x-transition.opacity.duration.200ms x-collapse class="px-5 pb-4 text-sm text-primary-dark/70 leading-relaxed">
                            <span x-text="[
                                'Standard shipping typically arrives within 3–7 business days. Express options are available at checkout.',
                                'Yes, you can return items within 30 days in original condition. Please see our returns policy for details.',
                                'Once shipped, we’ll email you a tracking link. You can also find tracking info in your account orders.',
                                'We accept major credit/debit cards, Apple Pay, Google Pay, and PayPal. All payments are securely processed.',
                                'We can modify or cancel orders before they ship. Contact support as soon as possible to request changes.',
                                'You can reach us via the contact form or email us at support@ruknshop.example. We typically respond within 24 hours.'
                            ][i-1]"></span>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- CTA -->
        <section>
            <div class="flex items-center justify-between bg-primary-gray/40 rounded-2xl p-6">
                <p class="text-primary-dark/80 text-sm">Still need help?</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-dark text-white text-sm font-semibold hover:bg-primary-dark/90 transition">Contact us</a>
            </div>
        </section>
    </div>
</x-app-layout>
