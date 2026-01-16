<header x-data="{ open: false }" class="relative z-40 bg-primary-light/95 backdrop-blur border-b border-primary-gray shadow-md">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-20 flex items-center justify-between">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}" class="transition-all duration-300 ease-out hover:-translate-y-1 hover:scale-110 hover:opacity-80">
                    <x-application-logo class="block h-10 w-auto fill-current text-primary-dark" />
                </a>
            </div>
            <!-- Navigation Links - Centered -->
            <div class="hidden sm:flex items-center justify-center flex-1">
                <div class="flex items-center space-x-8">

                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">{{ __('home.nav_home') }}</x-nav-link>

                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')">{{ __('home.nav_shop') }}</x-nav-link>

                    <x-nav-link :href="route('about')" :active="request()->routeIs('about')">{{ __('home.nav_about') }}</x-nav-link>

                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">{{ __('home.nav_contact') }}</x-nav-link>

                    @if(\Illuminate\Support\Facades\Route::has('deals'))
                    <x-nav-link :href="route('deals')" :active="request()->routeIs('deals')">{{ __('home.nav_deals') }}</x-nav-link>
                    @endif

                    @auth
                    @if(auth()->user()->isAdmin())
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">{{ __('home.nav_admin') }}</x-nav-link>
                    @endif
                    @endauth

                </div>

            </div>

            <!-- Settings Dropdown / Auth Links -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @php
                $initialCartCount = 0;
                if (Auth::check()) {
                $qtyCol = \Illuminate\Support\Facades\Schema::hasColumn('carts','quantity') ? 'quantity' : 'quanttty';
                $initialCartCount = \App\Models\Cart::where('user_id', Auth::id())->sum($qtyCol);
                } else {
                $initialCartCount = session('cart.count', 0);
                }
                @endphp
                <span id="cart-initial-count" class="hidden">{{ (int)$initialCartCount }}</span>
                <script>
                    document.addEventListener('alpine:init', () => {
                        const el = document.getElementById('cart-initial-count');
                        const initialCartCount = parseInt(el ? el.textContent : '0', 10);
                        if (!Alpine.store('cart')) {
                            Alpine.store('cart', {
                                count: initialCartCount
                            });
                        } else {
                            Alpine.store('cart').count = initialCartCount;
                        }
                    });
                </script>

                <!-- Language Selector -->
                <div class="relative" x-data="{ openLanguage: false }">
                    <button @click="openLanguage = !openLanguage" @keydown.escape.window="openLanguage = false" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-primary-dark hover:bg-primary-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-dark/20 transition-all duration-300 ease-out transform hover:scale-110 hover:shadow-lg" title="{{ __('home.nav_language') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium hidden sm:inline">{{ app()->getLocale() === 'ar' ? 'العربية' : (app()->getLocale() === 'nl' ? 'Nederlands' : 'English') }}</span>
                        <svg class="h-4 w-4 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openLanguage" @click.outside="openLanguage = false" x-transition class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg border border-primary-gray py-2 z-50">
                        <a href="{{ request()->fullUrlWithQuery(['locale' => 'nl']) }}" class="flex items-center gap-2 px-4 py-2 text-sm {{ app()->getLocale() === 'nl' ? 'bg-primary-dark text-white font-semibold' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200">
                            <span class="text-lg">🇳🇱</span>
                            <span>Nederlands</span>
                            @if(app()->getLocale() === 'nl')
                            <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </path>
                            </svg>
                            @endif
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['locale' => 'en']) }}" class="flex items-center gap-2 px-4 py-2 text-sm {{ app()->getLocale() === 'en' ? 'bg-primary-dark text-white font-semibold' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200">
                            <span class="text-lg">🇬🇧</span>
                            <span>English</span>
                            @if(app()->getLocale() === 'en')
                            <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </path>
                            </svg>
                            @endif
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['locale' => 'ar']) }}" class="flex items-center gap-2 px-4 py-2 text-sm {{ app()->getLocale() === 'ar' ? 'bg-primary-dark text-white font-semibold' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200">
                            <span class="text-lg">🇸🇦</span>
                            <span>العربية</span>
                            @if(app()->getLocale() === 'ar')
                            <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </path>
                            </svg>
                            @endif
                        </a>
                    </div>
                </div>

                <!-- Cart Icon with Badge -->
                <div class="relative flex items-center me-4">
                    <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center p-2 rounded-lg text-primary-dark hover:bg-primary-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-dark/20 transition-all duration-300 ease-out transform hover:scale-110 hover:shadow-lg {{ request()->routeIs('cart.*') ? 'bg-primary-dark text-white' : '' }}" title="{{ __('home.nav_cart') }}">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </a>
                    <span x-cloak x-show="$store.cart.count > 0" x-text="$store.cart.count" class="absolute -top-1 -right-1 bg-primary-dark text-white text-xs w-5 h-5 rounded-full flex items-center justify-center shadow-lg"></span>
                </div>

                @auth
                @php
                $user = Auth::user();
                $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
                @endphp
                <div class="relative" x-data="{ openProfile: false }">
                    <button @click="openProfile = !openProfile" @keydown.escape.window="openProfile = false" class="flex items-center gap-2 px-2 py-1 rounded-lg border border-primary-gray text-primary-dark bg-white hover:bg-primary-dark hover:text-white hover:border-primary-dark transition-all duration-300 transform hover:scale-105 hover:shadow-md group">
                        <div class="h-9 w-9 rounded-full overflow-hidden border border-neutral-200 flex items-center justify-center bg-primary-dark text-white text-sm font-semibold">
                            @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                            @else
                            <span>{{ $initial }}</span>
                            @endif
                        </div>
                        <div class="hidden sm:flex flex-col text-left leading-tight">
                            <span class="text-xs text-neutral-500 group-hover:text-white transition-colors duration-300">{{ __('home.nav_welcome_back') }}</span>
                            <span class="text-sm font-semibold text-primary-dark group-hover:text-white transition-colors duration-300">{{ Str::of($user->name)->before(' ') }}</span>
                        </div>
                        <svg class="w-4 h-4 text-neutral-500 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openProfile" @click.outside="openProfile = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-primary-gray py-2 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200">{{ __('home.nav_profile') }}</a>
                        @if(Route::has('orders.index'))
                        <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200">{{ __('home.nav_orders') }}</a>
                        @endif
                        @if($user->isAdmin())
                        <div class="border-t border-primary-gray my-1"></div>
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200">{{ __('home.nav_admin') }}</a>
                        @endif
                        <div class="border-t border-primary-gray my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-primary-dark hover:bg-red-500 hover:text-white transition-all duration-200">{{ __('home.nav_logout') }}</button>
                        </form>
                    </div>
                </div>
                @else
                <div class="flex items-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center p-2 rounded-lg text-primary-dark hover:bg-primary-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-dark/20 transition-all duration-300 ease-out transform hover:scale-110 hover:shadow-lg" title="{{ __('home.nav_login') }}">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-primary-dark hover:bg-primary-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-dark/20 transition-all duration-300 ease-out transform hover:scale-110 hover:shadow-lg">
                    <svg class="h-6 w-6 transition-transform duration-200" :class="{'rotate-90': open}" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-primary-light border-t border-primary-gray">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200 {{ request()->routeIs('home') ? 'bg-primary-dark text-white' : '' }}">{{ __('home.nav_home') }}</a>
            <a href="{{ route('products.index') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200 {{ request()->routeIs('products.index') ? 'bg-primary-dark text-white' : '' }}">{{ __('home.nav_shop') }}</a>
            <a href="{{ route('about') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200 {{ request()->routeIs('about') ? 'bg-primary-dark text-white' : '' }}">{{ __('home.nav_about') }}</a>
            <a href="{{ route('contact') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200 {{ request()->routeIs('contact') ? 'bg-primary-dark text-white' : '' }}">{{ __('home.nav_contact') }}</a>
            @if(\Illuminate\Support\Facades\Route::has('deals'))
            <a href="{{ route('deals') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200 {{ request()->routeIs('deals') ? 'bg-primary-dark text-white' : '' }}">{{ __('home.nav_deals') }}</a>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        @auth
        <div class="pt-4 pb-1 border-t border-primary-gray">
            <div class="px-4">
                <div class="font-medium text-base text-primary-dark">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-primary-dark">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200">{{ __('home.nav_profile') }}</a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-red-500 hover:text-white transition-all duration-200">{{ __('home.nav_logout') }}</a>
                </form>
            </div>
        </div>
        @else
        <div class="pt-4 pb-1 border-t border-primary-gray">
            <div class="mt-3 space-y-1">
                <a href="{{ route('login') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200 {{ request()->routeIs('login') ? 'bg-primary-dark text-white' : '' }}">{{ __('home.nav_login') }}</a>
                @if(\Illuminate\Support\Facades\Route::has('register'))
                <a href="{{ route('register') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200 {{ request()->routeIs('register') ? 'bg-primary-dark text-white' : '' }}">{{ __('home.nav_register') }}</a>
                @endif
            </div>
        </div>
        @endauth

        <!-- Cart Link in Mobile Menu -->
        <div class="pt-2 pb-3 border-t border-primary-gray">
            <a href="{{ route('cart.index') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-dark hover:text-white transition-all duration-200">{{ __('home.nav_cart') }}</a>
        </div>

        <!-- Language Selector in Mobile Menu -->
        <div class="pt-2 pb-3 border-t border-primary-gray">
            <div class="px-4 py-2 text-xs font-semibold text-primary-dark/60 uppercase tracking-widest mb-2">{{ __('home.nav_language') }}</div>
            <a href="{{ request()->fullUrlWithQuery(['locale' => 'nl']) }}" class="flex items-center gap-2 px-4 py-3 text-sm font-medium {{ app()->getLocale() === 'nl' ? 'bg-primary-dark text-white' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200">
                <span class="text-lg">🇳🇱</span>
                <span>Nederlands</span>
                @if(app()->getLocale() === 'nl')
                <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </path>
                </svg>
                @endif
            </a>
            <a href="{{ request()->fullUrlWithQuery(['locale' => 'en']) }}" class="flex items-center gap-2 px-4 py-3 text-sm font-medium {{ app()->getLocale() === 'en' ? 'bg-primary-dark text-white' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200">
                <span class="text-lg">🇬🇧</span>
                <span>English</span>
                @if(app()->getLocale() === 'en')
                <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </path>
                </svg>
                @endif
            </a>
            <a href="{{ request()->fullUrlWithQuery(['locale' => 'ar']) }}" class="flex items-center gap-2 px-4 py-3 text-sm font-medium {{ app()->getLocale() === 'ar' ? 'bg-primary-dark text-white' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200">
                <span class="text-lg">🇸🇦</span>
                <span>العربية</span>
                @if(app()->getLocale() === 'ar')
                <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </path>
                </svg>
                @endif
            </a>
        </div>
    </div>
</header>