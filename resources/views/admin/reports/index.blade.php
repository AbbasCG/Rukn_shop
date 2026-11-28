@extends('layouts.admin', ['title' => __('admin.reports.title')])

@php
    $totals = $data['totals'];
    $change = $data['change'];
    $buckets = $data['buckets'];
    $daily = $report->isDaily();
    $tz = \App\Support\SalesReport::TIMEZONE;

    // ---- Chart geometry (one series: sales per day or month) ----
    $W = 800; $H = 260; $left = 64; $right = 8; $top = 12; $bottom = 28;
    $plotW = $W - $left - $right; $plotH = $H - $top - $bottom;
    $maxValue = max(1.0, (float) collect($buckets)->max('revenue'));
    // A "nice" axis maximum and four steps.
    $magnitude = 10 ** floor(log10($maxValue / 4));
    $step = collect([1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10])->map(fn ($m) => $m * $magnitude)->first(fn ($s) => $s * 4 >= $maxValue);
    $axisMax = $step * 4;
    $n = max(1, count($buckets));
    $slot = $plotW / $n;
    $gap = $slot > 6 ? 2 : 1;                 // surface gap between bars
    $barW = max(1, $slot - $gap);
    $radius = min(4, $barW / 2);              // rounded data-end
    $labelEvery = (int) ceil($n / ($daily ? 10 : 12));
    $y = fn ($v) => $top + $plotH - ($v / $axisMax) * $plotH;

    $bucketLabel = fn ($b) => $daily
        ? $b['start']->copy()->locale(app()->getLocale())->translatedFormat('j M')
        : $b['start']->copy()->locale(app()->getLocale())->translatedFormat('M Y');
    $bucketLong = fn ($b) => $daily
        ? $b['start']->copy()->locale(app()->getLocale())->translatedFormat('l j F Y')
        : $b['start']->copy()->locale(app()->getLocale())->translatedFormat('F Y');

    $changeBadge = function (?float $pct) {
        if ($pct === null) {
            return ['text' => __('admin.reports.no_comparison'), 'class' => 'text-primary-dark/50', 'icon' => ''];
        }
        $up = $pct >= 0;

        return [
            'text' => __('admin.reports.vs_previous', ['change' => ($up ? '+' : '−') . number_format(abs($pct), 1) . '%']),
            'class' => $up ? 'text-emerald-700' : 'text-red-700',
            'icon' => $up ? '▲' : '▼',
        ];
    };
@endphp

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">{{ __('admin.reports.title') }}</h1>
            <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.reports.subtitle') }}</p>
        </div>
        <a href="{{ r('admin.orders.export', ['from' => $report->from->toDateString(), 'to' => $report->to->toDateString(), 'status' => 'all']) }}"
           class="inline-flex items-center rounded-lg border border-primary-gray bg-white px-4 py-2 text-sm font-semibold text-primary-dark hover:bg-primary-gray">
            {{ __('admin.reports.export') }}
        </a>
    </div>

    {{-- Period: one row of filters above the figures --}}
    <form method="GET" action="{{ r('admin.reports.index') }}" class="bg-white rounded-xl shadow p-4 flex flex-wrap items-end gap-3" x-data="{ range: @js($range) }">
        <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('admin.reports.period') }}">
            @foreach ($ranges as $option)
                <label class="cursor-pointer">
                    <input type="radio" name="range" value="{{ $option }}" x-model="range" class="sr-only peer"
                           @if ($option !== 'custom') onchange="this.form.submit()" @endif>
                    <span class="inline-block rounded-lg border px-3 py-1.5 text-sm font-medium border-primary-gray text-primary-dark peer-checked:bg-primary-dark peer-checked:text-white peer-checked:border-primary-dark peer-focus-visible:ring-2 peer-focus-visible:ring-primary-dark/30">{{ __('admin.reports.ranges.' . $option) }}</span>
                </label>
            @endforeach
        </div>
        <div class="flex flex-wrap items-end gap-2" x-show="range === 'custom'" x-cloak>
            <label class="text-xs text-primary-dark/70">{{ __('admin.reports.from') }}
                <input type="date" name="from" value="{{ $report->from->toDateString() }}" class="block mt-1 rounded-lg border border-primary-gray px-3 py-1.5 text-sm">
            </label>
            <label class="text-xs text-primary-dark/70">{{ __('admin.reports.to') }}
                <input type="date" name="to" value="{{ $report->to->toDateString() }}" class="block mt-1 rounded-lg border border-primary-gray px-3 py-1.5 text-sm">
            </label>
            <button type="submit" class="rounded-lg bg-primary-dark px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark/90">{{ __('admin.reports.show') }}</button>
        </div>
        <p class="w-full text-xs text-primary-dark/60">
            {{ $report->from->copy()->locale(app()->getLocale())->translatedFormat('j F Y') }} – {{ $report->to->copy()->locale(app()->getLocale())->translatedFormat('j F Y') }}
            · {{ __('admin.reports.compared_with', ['from' => $report->previous()->from->copy()->locale(app()->getLocale())->translatedFormat('j M Y'), 'to' => $report->previous()->to->copy()->locale(app()->getLocale())->translatedFormat('j M Y')]) }}
        </p>
        @if ($errors->any())
            <p class="w-full text-sm text-red-600">{{ $errors->first() }}</p>
        @endif
    </form>

    {{-- Headline figures --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach ([
            ['key' => 'revenue', 'label' => __('admin.reports.tiles.revenue'), 'value' => money($totals['revenue'])],
            ['key' => 'orders', 'label' => __('admin.reports.tiles.orders'), 'value' => number_format($totals['orders'])],
            ['key' => 'average', 'label' => __('admin.reports.tiles.average'), 'value' => money($totals['average'])],
        ] as $tile)
            @php $badge = $changeBadge($change[$tile['key']]); @endphp
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm font-medium text-primary-dark/60">{{ $tile['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-primary-dark" dir="ltr" style="text-align: start">{{ $tile['value'] }}</p>
                <p class="mt-1 text-xs font-medium {{ $badge['class'] }}"><span aria-hidden="true">{{ $badge['icon'] }}</span> {{ $badge['text'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['label' => __('admin.reports.tiles.excl_vat'), 'value' => $totals['excl_vat']],
            ['label' => __('admin.reports.tiles.vat'), 'value' => $totals['vat']],
            ['label' => __('admin.reports.tiles.shipping'), 'value' => $totals['shipping']],
            ['label' => __('admin.reports.tiles.discounts'), 'value' => $totals['discounts']],
        ] as $tile)
            <div class="bg-white rounded-xl shadow px-5 py-4">
                <p class="text-xs font-medium text-primary-dark/60">{{ $tile['label'] }}</p>
                <p class="mt-1 text-lg font-semibold text-primary-dark">{{ money($tile['value']) }}</p>
            </div>
        @endforeach
    </div>

    {{-- Chart: sales per day (or month) --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="text-lg font-semibold text-primary-dark">{{ $daily ? __('admin.reports.chart_daily') : __('admin.reports.chart_monthly') }}</h2>
        @if ($totals['orders'] === 0)
            <p class="mt-6 rounded-lg bg-primary-50 px-4 py-8 text-center text-sm text-primary-dark/70">{{ __('admin.reports.empty') }}</p>
        @else
            <div class="relative mt-4" x-data="{ tip: null }" @mouseleave="tip = null">
                <svg viewBox="0 0 {{ $W }} {{ $H }}" class="w-full h-auto" dir="ltr" role="img" aria-label="{{ $daily ? __('admin.reports.chart_daily') : __('admin.reports.chart_monthly') }}">
                    {{-- Recessive grid and value axis --}}
                    @for ($i = 0; $i <= 4; $i++)
                        @php $gy = $y($step * $i); @endphp
                        <line x1="{{ $left }}" x2="{{ $W - $right }}" y1="{{ $gy }}" y2="{{ $gy }}" stroke="#1F1D20" stroke-opacity="{{ $i === 0 ? 0.25 : 0.08 }}" stroke-width="1" />
                        <text x="{{ $left - 8 }}" y="{{ $gy + 4 }}" text-anchor="end" font-size="11" fill="#6B5F4E">€{{ number_format($step * $i, $step < 1 ? 2 : 0, ',', '.') }}</text>
                    @endfor

                    @foreach ($buckets as $i => $b)
                        @php
                            $x = $left + $i * $slot + $gap / 2;
                            $barTop = $y($b['revenue']);
                            $height = $top + $plotH - $barTop;
                        @endphp
                        @if ($b['revenue'] > 0)
                            {{-- Rounded at the data end, square on the baseline --}}
                            <path d="M{{ $x }},{{ $top + $plotH }} V{{ $barTop + $radius }} Q{{ $x }},{{ $barTop }} {{ $x + $radius }},{{ $barTop }} H{{ $x + $barW - $radius }} Q{{ $x + $barW }},{{ $barTop }} {{ $x + $barW }},{{ $barTop + $radius }} V{{ $top + $plotH }} Z"
                                  fill="#1F1D20" :fill-opacity="tip !== null && tip !== {{ $i }} ? 0.45 : 1" />
                        @endif
                        {{-- Hit target: the whole column, bigger than the bar --}}
                        <rect x="{{ $left + $i * $slot }}" y="{{ $top }}" width="{{ $slot }}" height="{{ $plotH }}" fill="transparent"
                              @mouseenter="tip = {{ $i }}" @focus="tip = {{ $i }}" tabindex="0">
                            <title>{{ $bucketLong($b) }}: {{ money($b['revenue']) }} · {{ trans_choice('admin.reports.orders_count', $b['orders'], ['count' => $b['orders']]) }}</title>
                        </rect>
                        @if ($i % $labelEvery === 0)
                            <text x="{{ $left + $i * $slot + $slot / 2 }}" y="{{ $H - 8 }}" text-anchor="middle" font-size="11" fill="#6B5F4E">{{ $bucketLabel($b) }}</text>
                        @endif
                    @endforeach
                </svg>

                {{-- Tooltip --}}
                @foreach ($buckets as $i => $b)
                    <div x-show="tip === {{ $i }}" x-cloak
                         class="pointer-events-none absolute top-0 z-10 -translate-x-1/2 rounded-lg bg-primary-dark px-3 py-2 text-xs text-white shadow-lg whitespace-nowrap"
                         style="left: {{ round((($left + $i * $slot + $slot / 2) / $W) * 100, 2) }}%">
                        <p class="font-semibold">{{ $bucketLong($b) }}</p>
                        <p>{{ money($b['revenue']) }} · {{ trans_choice('admin.reports.orders_count', $b['orders'], ['count' => $b['orders']]) }}</p>
                    </div>
                @endforeach
            </div>

            {{-- The same figures as a table --}}
            <details class="mt-4">
                <summary class="cursor-pointer text-sm font-medium text-primary-dark/80">{{ __('admin.reports.as_table') }}</summary>
                <div class="mt-3 max-h-80 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-primary-dark/70">
                            <tr>
                                <th class="py-2 pe-4 text-start font-medium">{{ $daily ? __('admin.reports.day') : __('admin.reports.month') }}</th>
                                <th class="py-2 pe-4 text-end font-medium">{{ __('admin.reports.tiles.orders') }}</th>
                                <th class="py-2 text-end font-medium">{{ __('admin.reports.tiles.revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_reverse($buckets) as $b)
                                <tr class="border-t border-primary-gray">
                                    <td class="py-1.5 pe-4">{{ $bucketLong($b) }}</td>
                                    <td class="py-1.5 pe-4 text-end">{{ $b['orders'] }}</td>
                                    <td class="py-1.5 text-end">{{ money($b['revenue']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endif
    </div>

    <p class="text-xs text-primary-dark/60">{{ __('admin.reports.definition') }}</p>
</div>
@endsection
