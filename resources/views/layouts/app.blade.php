<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" style="scrollbar-gutter: stable;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layouts.partials.seo')
    @stack('structured-data')

    <!-- Fonts -->
    @include('layouts.partials.fonts')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.arabic-fonts')
</head>

<body class="font-sans antialiased">
    @include('layouts.partials.toasts')

    <div class="min-h-screen">
        <!-- Page Heading -->
        @include('layouts.navigation')

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
        <!-- Page Footer -->
        @include('layouts.footer')

    </div>

</body>

</html>
