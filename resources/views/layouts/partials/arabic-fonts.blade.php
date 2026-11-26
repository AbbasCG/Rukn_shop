{{--
    Arabic typography. Included in every layout; outputs nothing for nl/en.

    Primary font: Thmanyah (خط ثمانية). Its license does not allow the files to
    be redistributed or modified, so they are not shipped with the project:
    download the font from Thmanyah and copy the .otf files, unchanged, into
    public/fonts/thmanyah/. Every file found there is registered automatically;
    family and weight are read from the file name (e.g. "...Sans-Bold.otf").

    Fallback: IBM Plex Sans Arabic (SIL OFL 1.1, self-hosted in
    public/fonts/ibm-plex-sans-arabic/), used until the Thmanyah files are
    present and for any glyph they do not cover.
--}}
@if (app()->getLocale() === 'ar')
    @php
        // Listing a folder of a few files is cheap, so this runs on every Arabic
        // page and new files are picked up without clearing any cache.
        $thmanyahFaces = (function () {
            $weights = [
                'extralight' => 200, 'ultralight' => 200, 'semibold' => 600, 'demibold' => 600,
                'extrabold' => 800, 'ultrabold' => 800, 'thin' => 100, 'light' => 300,
                'regular' => 400, 'normal' => 400, 'book' => 400, 'medium' => 500,
                'bold' => 700, 'heavy' => 800, 'black' => 900,
            ];
            $faces = [];

            foreach (glob(public_path('fonts/thmanyah/*.{otf,ttf,woff,woff2}'), GLOB_BRACE) ?: [] as $path) {
                $file = basename($path);
                $name = strtolower(preg_replace('/[^a-z]/i', '', pathinfo($file, PATHINFO_FILENAME)));

                $weight = 400;
                foreach ($weights as $keyword => $value) {
                    if (str_contains($name, $keyword)) {
                        $weight = $value;
                        break;
                    }
                }

                $family = match (true) {
                    str_contains($name, 'serif') && str_contains($name, 'display') => 'Thmanyah Serif Display',
                    str_contains($name, 'serif') && str_contains($name, 'text') => 'Thmanyah Serif Text',
                    str_contains($name, 'serif') => 'Thmanyah Serif',
                    default => 'Thmanyah Sans',
                };

                $format = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
                    'woff2' => 'woff2',
                    'woff' => 'woff',
                    'ttf' => 'truetype',
                    default => 'opentype',
                };

                $faces[] = [
                    'family' => $family,
                    'weight' => $weight,
                    'style' => str_contains($name, 'italic') ? 'italic' : 'normal',
                    'url' => asset('fonts/thmanyah/' . rawurlencode($file)),
                    'format' => $format,
                ];
            }

            return $faces;
        })();

        $plexWeights = [300, 400, 500, 600, 700];
        $plexRanges = [
            'arabic' => 'U+0600-06FF, U+0750-077F, U+0870-0891, U+0897-08E1, U+08E3-08FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE70-FE74, U+FE76-FEFC, U+102E0-102FB, U+10E60-10E7E, U+10EC2-10EC4, U+10EFC-10EFF, U+1EE00-1EEFF',
            'latin' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD',
        ];
    @endphp

    @foreach ($thmanyahFaces as $face)
        @if ($face['weight'] === 400 && $face['family'] === 'Thmanyah Sans' && $face['style'] === 'normal')
            <link rel="preload" href="{{ $face['url'] }}" as="font" type="font/{{ $face['format'] === 'opentype' ? 'otf' : $face['format'] }}" crossorigin>
        @endif
    @endforeach

    <style>
        @foreach ($thmanyahFaces as $face)
        @font-face {
            font-family: '{{ $face['family'] }}';
            font-style: {{ $face['style'] }};
            font-weight: {{ $face['weight'] }};
            font-display: swap;
            src: url('{{ $face['url'] }}') format('{{ $face['format'] }}');
        }
        @endforeach

        @foreach ($plexRanges as $subset => $range)
            @foreach ($plexWeights as $weight)
        @font-face {
            font-family: 'IBM Plex Sans Arabic';
            font-style: normal;
            font-weight: {{ $weight }};
            font-display: swap;
            src: url('{{ asset("fonts/ibm-plex-sans-arabic/ibm-plex-sans-arabic-{$subset}-{$weight}.woff2") }}') format('woff2');
            unicode-range: {{ $range }};
        }
            @endforeach
        @endforeach

        html:lang(ar) {
            --font-arabic: 'Thmanyah Sans', 'IBM Plex Sans Arabic', system-ui, sans-serif;
        }

        /* Beats Tailwind's .font-sans / .font-heading and the base h1–h6 rule. */
        html:lang(ar) body,
        html:lang(ar) .font-sans,
        html:lang(ar) .font-heading,
        html:lang(ar) :is(h1, h2, h3, h4, h5, h6),
        html:lang(ar) :is(input, select, textarea, button) {
            font-family: var(--font-arabic);
        }

        /* Letter-spacing breaks the joins between Arabic letters. */
        html:lang(ar) [class*="tracking-"] {
            letter-spacing: 0;
        }
    </style>
@endif
