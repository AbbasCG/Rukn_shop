@php
    $paths = [
        'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6',
        'archive-box' => 'M3 8l1-3h16l1 3m-18 0h18M5 8v10a1 1 0 001 1h12a1 1 0 001-1V8M9 12h6',
        'tag' => 'M7 7h4l6 6-4 4-6-6V7z',
        'shopping-bag' => 'M6 7l1 12h10l1-12H6zm3 0V5a3 3 0 116 0v2',
        'users' => 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2m14-10a4 4 0 10-8 0 4 4 0 008 0z',
        'cog' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'chart-bar' => 'M3 3v18h18M8 17V9m4 8V5m4 12v-6',
    ];
@endphp
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $paths[$name] ?? $paths['home'] }}" />
</svg>
