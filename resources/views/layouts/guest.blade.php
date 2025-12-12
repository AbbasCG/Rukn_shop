<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tilt-warp:400&family=signika:300,400,500,600,700&display=swap" rel="stylesheet" />
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('storage/images/favicon.svg') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-primary-50 dark:bg-primary-50 text-primary-800 dark:text-primary-800 flex flex-col h-[100vh]">
    {{-- Header altijd full width --}}
    <header class="w-full">
        @include('layouts.navigation')
    </header>

    {{-- Main: neemt de ruimte in & centreert alleen de card --}}
    <main class="flex-1 w-full flex justify-center items-center px-4 sm:px-6 lg:px-8 py-28">
        <div class="w-full max-w-md mx-auto bg-white dark:bg-white rounded-2xl shadow-xl p-8 md:p-10">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer ook full width --}}
    <footer class="w-full">
        @include('layouts.footer')
    </footer>
</body>
</html>
