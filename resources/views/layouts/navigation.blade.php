<nav x-data="{ open: false }" class="bg-primary-light border-b border-primary-gray">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}">
                    <x-application-logo class="block h-9 w-auto fill-current text-primary-dark" />
                </a>
            </div>

            <!-- Navigation Links - Centered -->
            <div class="hidden sm:flex items-center justify-center flex-1">
    <div class="flex items-center space-x-8">

        <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
            Home
        </x-nav-link>

        <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')">
            Products
        </x-nav-link>

        <x-nav-link :href="route('about')" :active="request()->routeIs('about')">
            About Us
        </x-nav-link>

        <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">
            Contact
        </x-nav-link>

        @auth
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-nav-link>
        @endauth

    </div>
         
            </div>

            <!-- Settings Dropdown / Auth Links -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                
                    <!-- Cart Icon -->
                    <div class="flex items-center me-4">
                        <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center p-2 rounded-md text-primary-dark hover:text-primary-dark hover:bg-primary-gray focus:outline-none focus:bg-primary-gray focus:text-primary-dark transition duration-150 ease-in-out {{ request()->routeIs('cart.*') ? 'text-primary-dark bg-primary-gray' : '' }}" title="{{ __('Shopping Cart') }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                    </div>
                    
                    @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-dark bg-primary-light hover:text-primary-dark focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center p-2 rounded-md text-primary-dark hover:text-primary-dark hover:bg-primary-gray focus:outline-none focus:bg-primary-gray focus:text-primary-dark transition duration-150 ease-in-out {{ request()->routeIs('login') ? 'text-primary-dark bg-primary-gray' : '' }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-primary-dark hover:text-primary-dark hover:bg-primary-gray focus:outline-none focus:bg-primary-gray focus:text-primary-dark transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Home') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-primary-gray">
                <div class="px-4">
                    <div class="font-medium text-base text-primary-dark">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-primary-dark">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-primary-gray">
                <div class="mt-3 space-y-1">
                    <a href="{{ route('login') }}" class="flex items-center px-4 py-2 text-sm font-medium leading-5 text-primary-dark hover:text-primary-dark hover:bg-primary-gray focus:outline-none focus:bg-primary-gray focus:text-primary-dark transition duration-150 ease-in-out {{ request()->routeIs('login') ? 'text-primary-dark bg-primary-gray' : '' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('Log in') }}
                    </a>
                </div>
            </div>
        @endauth

        <!-- Cart Link in Mobile Menu -->
        @auth
            <div class="pt-2 pb-3 border-t border-primary-gray">
                <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">
                    <svg class="h-5 w-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('Shopping Cart') }}
                </x-responsive-nav-link>
            </div>
        @endauth
    </div>
</nav>

        