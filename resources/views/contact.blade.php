<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16" style="font-family: 'Signika', sans-serif;">
        <style>
            @media (prefers-reduced-motion: no-preference) {
                @keyframes fade-in-up { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
                .animate-fade-in-up { animation: fade-in-up 600ms ease-out both; }
            }
        </style>

        <!-- 1) Hero / Intro -->
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-12 text-center md:text-left animate-fade-in-up">
            <div class="space-y-4">
                <h1 class="text-4xl font-bold text-primary-dark">Contact Us</h1>
                <p class="text-neutral-700">We’re here to help you 7 days a week.</p>
            </div>
        </section>

        <!-- 2) Two-Column Layout -->
        <section>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- LEFT (Form) -->
                <div class="lg:col-span-2 backdrop-blur-xl bg-white/70 shadow-xl rounded-3xl border border-white/20 p-8 space-y-6">
                    <form method="POST" action="#" onsubmit="return false;">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-xs font-semibold text-neutral-700 mb-1">Name</label>
                                <input id="name" type="text" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" placeholder="Your name" />
                            </div>
                            <div>
                                <label for="email" class="block text-xs font-semibold text-neutral-700 mb-1">Email</label>
                                <input id="email" type="email" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" placeholder="you@example.com" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="subject" class="block text-xs font-semibold text-neutral-700 mb-1">Subject</label>
                                <input id="subject" type="text" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" placeholder="How can we help?" />
                            </div>
                            <div>
                                <label for="message" class="block text-xs font-semibold text-neutral-700 mb-1">Message</label>
                                <textarea id="message" rows="6" class="w-full rounded-xl bg-white/70 border border-neutral-300 px-4 py-3 shadow-sm focus:ring-primary-dark/30 focus:border-primary-dark transition" placeholder="Write your message..."></textarea>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full md:w-auto inline-flex items-center px-6 py-3 rounded-xl bg-primary-dark text-white font-semibold shadow-lg hover:bg-primary-dark/90 transition">Send Message</button>
                        </div>
                    </form>
                </div>

                <!-- RIGHT (Info Card) -->
                <div class="bg-white shadow-xl rounded-3xl p-8 space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 text-primary-dark/80">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <div>
                                <p class="text-sm font-semibold">Email</p>
                                <p class="text-xs">support@ruknshop.example</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-primary-dark/80">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m0 4v10m6-10h6m-6 0v10"/></svg>
                            <div>
                                <p class="text-sm font-semibold">Phone</p>
                                <p class="text-xs">+1 (555) 123-4567</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-primary-dark/80">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12m0 0l4.243-4.243M13.414 12H3"/></svg>
                            <div>
                                <p class="text-sm font-semibold">Address</p>
                                <p class="text-xs">123 Rukn Street, Shop City, Country</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-primary-dark/80">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"/></svg>
                            <div>
                                <p class="text-sm font-semibold">Opening Hours</p>
                                <p class="text-xs">Mon–Sun: 9:00–18:00</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3) Map Placeholder -->
        <section>
            <div class="h-56 rounded-3xl bg-primary-gray/40 flex items-center justify-center text-neutral-600 shadow-lg">
                Map coming soon
            </div>
        </section>
    </div>
</x-app-layout>