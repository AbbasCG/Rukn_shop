{{--
    Shared layout for error pages. Deliberately self-contained: a 404 for an
    unknown URL is rendered before the session and SetLocale middleware run,
    so nothing here may rely on the session, CSRF token or the current user.
    Expects: $code, and optionally $showShop.
--}}
@php
    $supported = config('app.supported_locales', ['nl', 'en', 'ar']);
    $segment = request()->segment(1);
    $locale = in_array($segment, $supported, true)
        ? $segment
        : (in_array(app()->getLocale(), $supported, true) ? app()->getLocale() : 'nl');
    app()->setLocale($locale);
    $isRtl = $locale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __("errors.$code.title") }} – {{ __('seo.site_name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css'])
    @include('layouts.partials.arabic-fonts')
</head>
<body class="font-sans antialiased bg-primary-light text-primary-dark">
    <main class="min-h-screen flex flex-col items-center justify-center px-4 py-16 text-center">
        <a href="{{ url('/' . $locale) }}" class="mb-12" aria-label="{{ __('seo.site_name') }}">
            <img src="{{ asset('images/dark-logo.svg') }}" alt="{{ __('seo.site_name') }}" class="h-12 w-auto">
        </a>

        <p class="text-sm font-semibold text-primary-dark/50">{{ __('errors.code', ['code' => $code]) }}</p>
        <h1 class="mt-2 text-3xl md:text-4xl font-bold">{{ __("errors.$code.title") }}</h1>
        <p class="mt-4 max-w-md text-primary-dark/70 leading-relaxed">{{ __("errors.$code.message") }}</p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ url('/' . $locale) }}" class="btn-primary">{{ __('errors.home') }}</a>
            @if($showShop ?? false)
                <a href="{{ url('/' . $locale . '/products') }}" class="btn-secondary">{{ __('errors.shop') }}</a>
            @endif
            @if($showContact ?? false)
                <a href="{{ url('/' . $locale . '/contact') }}" class="btn-secondary">{{ __('errors.contact') }}</a>
            @endif
        </div>
    </main>
</body>
</html>
