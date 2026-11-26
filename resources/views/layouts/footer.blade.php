    <!-- 8. FOOTER -->
    <footer class="bg-primary-dark text-primary-light py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div class="text-start">
                    <a href="{{ r('home') }}" class="inline-block mb-4 hover:opacity-80 transition-opacity duration-300">
                        <img src="{{ asset('images/Voll logo.svg') }}" alt="Rukn Shop" class="h-12 w-auto">
                    </a>
                    <p class="text-primary-light/70 text-sm leading-relaxed">
                        {{ __('footer.brand.description') }}
                    </p>
                </div>

                <!-- Shop -->
                <div class="text-start">
                    <h4 class="font-semibold text-primary-light mb-4">{{ __('footer.sections.shop') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ r('products.index') }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.all_products') }}</a></li>
                        <li><a href="#" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.new_arrivals') }}</a></li>
                        <li><a href="#" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.bestsellers') }}</a></li>
                        <li><a href="#" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.sale') }}</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="text-start">
                    <h4 class="font-semibold text-primary-light mb-4">{{ __('footer.sections.support') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ r('contact') }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.contact') }}</a></li>
                        <li><a href="{{ r('faq') }}#faq-shipping" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.shipping') }}</a></li>
                        <li><a href="{{ r('faq') }}#faq-returns" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.returns') }}</a></li>
                        <li><a href="{{ r('orders.lookup') }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.find_order') }}</a></li>
                        <li><a href="{{ r('faq') }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.faq') }}</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="text-start">
                    <h4 class="font-semibold text-primary-light mb-4">{{ __('footer.sections.follow') }}</h4>
                    <div class="flex gap-4">
                        <!-- Facebook -->
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary-light/10 hover:bg-primary-light/20 transition-colors duration-300" aria-label="Facebook">
                            <svg class="w-5 h-5 text-primary-light" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>

                        <!-- Instagram -->
                        <a href="https://www.instagram.com/rukn_30/?utm_source=ig_web_button_share_sheet" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary-light/10 hover:bg-primary-light/20 transition-colors duration-300" aria-label="Instagram">
                            <svg class="w-5 h-5 text-primary-light" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.322a1.44 1.44 0 110-2.881 1.44 1.44 0 010 2.881z"/>
                            </svg>
                        </a>

                        <!-- Twitter -->
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary-light/10 hover:bg-primary-light/20 transition-colors duration-300" aria-label="Twitter">
                            <svg class="w-5 h-5 text-primary-light" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="border-t border-primary-light/20 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-primary-light/70">
                <p>{{ __('footer.copyright', ['year' => date('Y')]) }}</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <a href="{{ r('privacy-policy') }}" class="hover:text-primary-light transition-colors duration-300">{{ __('footer.links.privacy') }}</a>
                    <a href="{{ r('terms-of-service') }}" class="hover:text-primary-light transition-colors duration-300">{{ __('footer.links.terms') }}</a>
                    <a href="{{ r('cookies') }}" class="hover:text-primary-light transition-colors duration-300">{{ __('footer.links.cookies') }}</a>
                    <a href="{{ r('withdrawal-form') }}" class="hover:text-primary-light transition-colors duration-300">{{ __('footer.links.withdrawal') }}</a>
                </div>
            </div>
        </div>
    </footer>
