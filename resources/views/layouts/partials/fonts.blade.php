{{--
    Latin fonts, served from our own server (public/fonts) so that no third
    party receives visitors' IP addresses. Signika (text) and Tilt Warp
    (headings), both SIL Open Font License 1.1 (OFL.txt next to the files),
    taken unchanged from Fontsource. Browsers only download the subset
    (latin / latin-ext) a page actually needs.
--}}
@php
    $latinRanges = [
        'latin' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD',
        'latin-ext' => 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF',
    ];
@endphp
@if (app()->getLocale() !== 'ar')
    <link rel="preload" href="{{ asset('fonts/signika/signika-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
@endif
<style>
    @foreach ($latinRanges as $subset => $range)
        @foreach ([300, 400, 500, 600, 700] as $weight)
    @font-face {
        font-family: 'Signika';
        font-style: normal;
        font-weight: {{ $weight }};
        font-display: swap;
        src: url('{{ asset("fonts/signika/signika-{$subset}-{$weight}-normal.woff2") }}') format('woff2');
        unicode-range: {{ $range }};
    }
        @endforeach
    @font-face {
        font-family: 'Tilt Warp';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url('{{ asset("fonts/tilt-warp/tilt-warp-{$subset}-400-normal.woff2") }}') format('woff2');
        unicode-range: {{ $range }};
    }
    @endforeach
</style>
