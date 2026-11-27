    <!-- 8. FOOTER -->
    <footer class="bg-primary-dark text-primary-light py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div class="text-start">
                    <a href="{{ r('home') }}" class="inline-block mb-4 hover:opacity-80 transition-opacity duration-300">
                        <img src="{{ asset('images/Voll logo.svg') }}" alt="Rukn" class="h-12 w-auto">
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
                        <li><a href="{{ r('products.index', ['sort' => 'newest']) }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.new_arrivals') }}</a></li>
                        <li><a href="{{ r('products.index', ['sort' => 'popular']) }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">{{ __('footer.links.bestsellers') }}</a></li>
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

                <!-- Social Media (only accounts set in config/shop.php) -->
                @php
                        $socialAccounts = [
                            'instagram' => ['label' => 'Instagram', 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.322a1.44 1.44 0 110-2.881 1.44 1.44 0 010 2.881z'],
                            'facebook' => ['label' => 'Facebook', 'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
                            'x' => ['label' => 'X', 'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
                            'tiktok' => ['label' => 'TikTok', 'path' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z'],
                        ];
                    $socialAccounts = array_filter($socialAccounts, fn ($network) => filled(config('shop.social.' . $network)), ARRAY_FILTER_USE_KEY);
                @endphp
                @if ($socialAccounts)
                <div class="text-start">
                    <h4 class="font-semibold text-primary-light mb-4">{{ __('footer.sections.follow') }}</h4>
                    <div class="flex gap-4">
                        @foreach ($socialAccounts as $network => $account)
                                <a href="{{ config('shop.social.' . $network) }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary-light/10 hover:bg-primary-light/20 transition-colors duration-300" aria-label="{{ $account['label'] }}">
                                    <svg class="w-5 h-5 text-primary-light" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="{{ $account['path'] }}"/>
                                    </svg>
                                </a>
                        @endforeach
                    </div>
                </div>
                @endif
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
