    <!-- 8. FOOTER -->
    <footer class="bg-primary-dark text-primary-light py-16">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-full mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div>
                    <a href="{{ route('home') }}" class="inline-block mb-4 hover:opacity-80 transition-opacity duration-300">
                        <img src="{{ asset('images/Voll logo.svg') }}" alt="Rukn Shop" class="h-12 w-auto">
                    </a>
                    <p class="text-primary-light/70 text-sm leading-relaxed">
                        Elegance woven into every moment. Discover timeless pieces crafted with passion and authenticity.
                    </p>
                </div>

                <!-- Shop -->
                <div>
                    <h4 class="font-semibold text-primary-light mb-4">Shop</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('products.index') }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">All Products</a></li>
                        <li><a href="#" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">New Arrivals</a></li>
                        <li><a href="#" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">Bestsellers</a></li>
                        <li><a href="#" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">Sale</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="font-semibold text-primary-light mb-4">Support</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('contact') }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">Contact Us</a></li>
                        <li><a href="#" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">Shipping Info</a></li>
                        <li><a href="#" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">Returns</a></li>
                        <li><a href="{{ route('faq') }}" class="nav-link-underline text-primary-light/70 hover:text-primary-light transition-colors duration-300">FAQ</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div>
                    <h4 class="font-semibold text-primary-light mb-4">Follow Us</h4>
                    <div class="flex gap-4">
                        <!-- Facebook -->
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary-light/10 hover:bg-primary-light/20 transition-colors duration-300" aria-label="Facebook">
                            <svg class="w-5 h-5 text-primary-light" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>

                        <!-- Instagram -->
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary-light/10 hover:bg-primary-light/20 transition-colors duration-300" aria-label="Instagram">
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

                        <!-- LinkedIn -->
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary-light/10 hover:bg-primary-light/20 transition-colors duration-300" aria-label="LinkedIn">
                            <svg class="w-5 h-5 text-primary-light" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.475-2.236-1.986-2.236-1.081 0-1.722.722-2.006 1.419-.103.249-.129.597-.129.946v5.44h-3.553s.047-8.842 0-9.763h3.553v1.381c.43-.664 1.199-1.609 2.918-1.609 2.133 0 3.732 1.39 3.732 4.377v5.614zM5.337 8.855c-1.144 0-1.915-.762-1.915-1.715 0-.957.77-1.715 1.958-1.715 1.187 0 1.914.758 1.939 1.715 0 .953-.752 1.715-1.982 1.715zm1.946 11.597H3.392V9.689h3.891v10.763zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="border-t border-primary-light/20 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-primary-light/70">
                <p>© 2025 Rukn Shop. All rights reserved.</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <a href="{{ route('privacy-policy') }}" class="hover:text-primary-light transition-colors duration-300">Privacy Policy</a>
                    <a href="{{ route('terms-of-service') }}" class="hover:text-primary-light transition-colors duration-300">Terms of Service</a>
                    <a href="#" class="hover:text-primary-light transition-colors duration-300">Cookies</a>
                </div>
            </div>
        </div>
    </footer>