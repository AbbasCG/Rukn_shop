@props(['active' => false])

@php
    $classes = ($active ?? false)
        ? 'relative inline-flex items-center px-3 py-2 text-base font-medium leading-5 text-white bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-dark/20 rounded-lg transition-all duration-300'
        : 'relative inline-flex items-center px-3 py-2 text-base font-medium leading-5 text-primary-dark/80 hover:text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-dark/20 rounded-lg transition-all duration-300 transform hover:scale-105';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <span class="relative inline-block transition-all duration-300">
        <span>{{ $slot }}</span>
    </span>
</a>
