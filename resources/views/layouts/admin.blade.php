@props(['title' => 'Admin Panel'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | Rukn Shop Admin</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tilt-warp:400&family=signika:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-primary-50 dark:bg-primary-50 text-primary-800 dark:text-primary-800 min-h-screen" style="font-family: 'Signika', sans-serif;">
    @include('layouts.partials.toasts')

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Top bar -->
            <header class="h-30 px-4 flex items-center justify-between border-b border-neutral-200 dark:border-neutral-200 bg-white dark:bg-white backdrop-blur sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button class="md:hidden p-2 rounded-lg bg-primary-800 text-white" @click="sidebarOpen=true">
                        <svg class="w-5 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold">{{ $title }}</h1>
                </div>
                <div class="flex items-center gap-3" x-data="{ openAdminProfile: false, openAdminLanguage: false }">
                    <div class="relative">
                        <button
                            @click="openAdminLanguage = !openAdminLanguage"
                            @keydown.escape.window="openAdminLanguage = false"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-neutral-200 bg-white text-primary-800 hover:bg-neutral-100 transition-all"
                            title="Language"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium hidden sm:inline">{{ app()->getLocale() === 'ar' ? 'العربية' : (app()->getLocale() === 'en' ? 'English' : 'Nederlands') }}</span>
                            <span class="text-xs font-semibold uppercase sm:hidden">{{ app()->getLocale() }}</span>
                            <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="openAdminLanguage"
                            @click.outside="openAdminLanguage = false"
                            x-transition
                            class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-44 bg-white rounded-xl shadow-lg border border-neutral-200 py-2 z-50"
                            style="display: none;"
                        >
                            <a href="{{ localizePath('nl') }}" class="flex items-center gap-2 px-3 py-2 text-sm {{ app()->getLocale() === 'nl' ? 'bg-primary-800 text-white font-semibold' : 'text-primary-800 hover:bg-neutral-100' }} transition-colors">
                                <span class="text-base">🇳🇱</span>
                                <span>Nederlands</span>
                            </a>
                            <a href="{{ localizePath('ar') }}" class="flex items-center gap-2 px-3 py-2 text-sm {{ app()->getLocale() === 'ar' ? 'bg-primary-800 text-white font-semibold' : 'text-primary-800 hover:bg-neutral-100' }} transition-colors">
                                <span class="text-base">🇸🇦</span>
                                <span>العربية</span>
                            </a>
                            <a href="{{ localizePath('en') }}" class="flex items-center gap-2 px-3 py-2 text-sm {{ app()->getLocale() === 'en' ? 'bg-primary-800 text-white font-semibold' : 'text-primary-800 hover:bg-neutral-100' }} transition-colors">
                                <span class="text-base">🇬🇧</span>
                                <span>English</span>
                            </a>
                        </div>
                    </div>

                    <button class="p-3 rounded-lg border border-neutral-200 dark:border-neutral-200 hover:bg-primary-50 transition-all" @click="darkMode = !darkMode">
                        <svg x-show="!darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z" />
                        </svg>
                    </button>

                    @php
                    $adminUser = auth()->user();
                    $adminInitial = strtoupper(substr($adminUser->name ?? 'U', 0, 1));
                    @endphp
                    <div class="relative">
                        <button @click="openAdminProfile = !openAdminProfile" @keydown.escape.window="openAdminProfile = false" class="flex items-center gap-2 px-2 py-1 rounded-lg border border-neutral-200 text-primary-800 bg-white hover:bg-neutral-100 transition duration-200">
                            <div class="h-9 w-9 rounded-full overflow-hidden border border-neutral-200 flex items-center justify-center bg-primary-800 text-white text-sm font-semibold">
                                @if($adminUser->avatar ?? false)
                                <img src="{{ asset('storage/' . $adminUser->avatar) }}" alt="{{ $adminUser->name }}" class="h-full w-full object-cover">
                                @else
                                <span>{{ $adminInitial }}</span>
                                @endif
                            </div>
                            <div class="hidden sm:flex flex-col text-left leading-tight">
                                <span class="text-xs text-neutral-500">Admin</span>
                                <span class="text-sm font-semibold text-primary-dark">{{ \Illuminate\Support\Str::of($adminUser->name)->before(' ') }}</span>
                            </div>
                            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openAdminProfile" @click.outside="openAdminProfile = false" x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-neutral-100 py-2 z-50">
                            <a href="{{ r('profile.edit') }}" class="block px-4 py-2 text-sm text-primary-dark hover:bg-neutral-100">Profile</a>
                            <a href="{{ r('home') }}" class="block px-4 py-2 text-sm text-primary-dark hover:bg-neutral-100">Back to Shop</a>
                            <div class="border-t border-neutral-200 my-1"></div>
                            <form method="POST" action="{{ r('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-primary-dark hover:bg-neutral-100">Log Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-6 bg-primary-50 dark:bg-primary-50 min-h-full">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
