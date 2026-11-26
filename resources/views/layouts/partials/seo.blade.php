{{--
    Title, description, canonical, hreflang, Open Graph and Twitter tags.
    Expects (all optional): $title, $description, $image, $type, $noindex.
--}}
@php
    $siteName = __('seo.site_name');
    $pageTitle = filled($title ?? null) ? $title . ' – ' . $siteName : __('seo.home_title');
    $pageDescription = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($description ?? policy_text('seo.default_description')))), 160, '…');
    $pageImage = $image ?? asset('images/banner.jpg');
    $locales = config('app.supported_locales', ['nl', 'en', 'ar']);
    $ogLocales = ['nl' => 'nl_NL', 'en' => 'en_GB', 'ar' => 'ar_AR'];

    // x-default points at Dutch, like the redirect from "/".
    // Canonical: drop sorting, price filters and search, but keep the parameters
    // that change the content (a category page, a later page of results).
    $kept = array_filter([
        'category' => request()->query('category'),
        'page' => (int) request()->query('page', 1) > 1 ? (int) request()->query('page') : null,
    ], fn ($value) => is_scalar($value) && $value !== '');
    $pageQuery = $kept ? '?' . http_build_query($kept) : '';
    $canonical = url()->current() . $pageQuery;
    $robots = ($noindex ?? false) || filled(request()->query('q')) ? 'noindex, follow' : 'index, follow';
@endphp
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

@unless ($noindex ?? false)
    @foreach ($locales as $locale)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ url(localizePath($locale)) . $pageQuery }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url(localizePath('nl')) . $pageQuery }}">
@endunless

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $type ?? 'website' }}">
<meta property="og:title" content="{{ filled($title ?? null) ? $title : __('seo.home_title') }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:locale" content="{{ $ogLocales[app()->getLocale()] ?? 'nl_NL' }}">
@foreach ($locales as $locale)
    @if ($locale !== app()->getLocale())
        <meta property="og:locale:alternate" content="{{ $ogLocales[$locale] }}">
    @endif
@endforeach
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ filled($title ?? null) ? $title : __('seo.home_title') }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $pageImage }}">
