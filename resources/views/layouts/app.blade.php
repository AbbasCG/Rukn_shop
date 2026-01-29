<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tilt-warp:400&family=signika:300,400,500,600,700&display=swap" rel="stylesheet" />
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen">
        <!-- Page Heading -->
  
        <header class="" style="height: 80px;">
            @include('layouts.navigation')
        </header>

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
        <!-- Page Footer -->
        <footer>
            @include('layouts.footer')
        </footer>

    </div>

</body>

</html>