@props(['active' => false])

@php
    $classes = ($active ?? false)
        ? 'group relative inline-flex items-center px-1 py-1 text-base font-medium leading-5 text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-dark/20 rounded-md'
        : 'group relative inline-flex items-center px-1 py-1 text-base font-medium leading-5 text-primary-dark/80 hover:text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-dark/20 rounded-md transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <span class="relative inline-block pb-1 transition-transform duration-200 group-hover:-translate-y-0.5">
        <span>{{ $slot }}</span>
        <span class="absolute left-0 -bottom-0.5 h-0.5 w-full bg-primary-dark origin-left transition-transform duration-200 ease-out {{ $active ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
    </span>
</a>
