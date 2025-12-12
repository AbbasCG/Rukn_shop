<header x-data="{ open: false }" class="fixed top-0 inset-x-0 z-50 bg-primary-light/95 backdrop-blur border-b border-primary-gray shadow-md">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-20 flex items-center justify-between">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}" class="transition-transform duration-200 ease-out hover:-translate-y-0.5">
                    <x-application-logo class="block h-10 w-auto fill-current text-primary-dark" />
                </a>
            </div>
            <!-- Navigation Links - Centered -->
            <div class="hidden sm:flex items-center justify-center flex-1">
                <div class="flex items-center space-x-8">

                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-nav-link>

                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')">Shop</x-nav-link>

                    <x-nav-link :href="route('about')" :active="request()->routeIs('about')">About Us</x-nav-link>

                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>

                    @if(\Illuminate\Support\Facades\Route::has('deals'))
                        <x-nav-link :href="route('deals')" :active="request()->routeIs('deals')">Deals</x-nav-link>
                    @endif

                    @auth
                        @if(auth()->user()->isAdmin())
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">Admin Panel</x-nav-link>
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
                            Alpine.store('cart', { count: initialCartCount });
                        } else {
                            Alpine.store('cart').count = initialCartCount;
                        }
                    });
                </script>

                <!-- Cart Icon with Badge -->
                <div class="relative flex items-center me-4">
                    <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center p-2 rounded-lg text-primary-dark hover:bg-primary-gray focus:outline-none focus:ring-2 focus:ring-primary-dark/20 transition duration-200 ease-out transform hover:scale-105 {{ request()->routeIs('cart.*') ? 'bg-primary-gray' : '' }}" title="{{ __('Shopping Cart') }}">
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
                    <button @click="openProfile = !openProfile" @keydown.escape.window="openProfile = false" class="flex items-center gap-2 px-2 py-1 rounded-lg border border-primary-gray text-primary-dark bg-white hover:bg-primary-gray transition duration-200">
                        <div class="h-9 w-9 rounded-full overflow-hidden border border-neutral-200 flex items-center justify-center bg-primary-dark text-white text-sm font-semibold">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                            @else
                                <span>{{ $initial }}</span>
                            @endif
                        </div>
                        <div class="hidden sm:flex flex-col text-left leading-tight">
                            <span class="text-xs text-neutral-500">Welcome back,</span>
                            <span class="text-sm font-semibold text-primary-dark">{{ Str::of($user->name)->before(' ') }}</span>
                        </div>
                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="openProfile" @click.outside="openProfile = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-primary-gray py-2 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-primary-dark hover:bg-primary-gray">Profile</a>
                        @if(Route::has('orders.index'))
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-primary-dark hover:bg-primary-gray">My Orders</a>
                        @endif
                        @if($user->isAdmin())
                            <div class="border-t border-primary-gray my-1"></div>
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-primary-dark hover:bg-primary-gray">Admin Dashboard</a>
                        @endif
                        <div class="border-t border-primary-gray my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-primary-dark hover:bg-primary-gray">Log Out</button>
                        </form>
                    </div>
                </div>
                @else
                <div class="flex items-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg border border-primary-dark text-primary-dark hover:bg-primary-dark hover:text-white transition duration-200 ease-out">
                        Log in
                    </a>
                    @if(\Illuminate\Support\Facades\Route::has('register'))
                        <a href="{{ route('register') }}" class="ms-2 inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg border border-primary-gray text-primary-dark hover:bg-primary-gray transition duration-200 ease-out">
                            Register
                        </a>
                    @endif
                </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-primary-dark hover:bg-primary-gray focus:outline-none focus:ring-2 focus:ring-primary-dark/20 transition duration-200 ease-out transform hover:scale-105">
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
            <a href="{{ route('home') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition {{ request()->routeIs('home') ? 'bg-primary-gray/60' : '' }}">Home</a>
            <a href="{{ route('products.index') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition {{ request()->routeIs('products.index') ? 'bg-primary-gray/60' : '' }}">Shop</a>
            <a href="{{ route('about') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition {{ request()->routeIs('about') ? 'bg-primary-gray/60' : '' }}">About Us</a>
            <a href="{{ route('contact') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition {{ request()->routeIs('contact') ? 'bg-primary-gray/60' : '' }}">Contact</a>
            @if(\Illuminate\Support\Facades\Route::has('deals'))
                <a href="{{ route('deals') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition {{ request()->routeIs('deals') ? 'bg-primary-gray/60' : '' }}">Deals</a>
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
                <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition">Profile</a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition">Log Out</a>
                </form>
            </div>
        </div>
        @else
        <div class="pt-4 pb-1 border-t border-primary-gray">
            <div class="mt-3 space-y-1">
                <a href="{{ route('login') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition {{ request()->routeIs('login') ? 'bg-primary-gray/60' : '' }}">Log in</a>
                @if(\Illuminate\Support\Facades\Route::has('register'))
                    <a href="{{ route('register') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition {{ request()->routeIs('register') ? 'bg-primary-gray/60' : '' }}">Register</a>
                @endif
            </div>
        </div>
        @endauth

        <!-- Cart Link in Mobile Menu -->
        <div class="pt-2 pb-3 border-t border-primary-gray">
            <a href="{{ route('cart.index') }}" class="block px-4 py-3 text-sm font-medium text-primary-dark hover:bg-primary-gray/60 transition">Shopping Cart</a>
        </div>
    </div>
</header>
