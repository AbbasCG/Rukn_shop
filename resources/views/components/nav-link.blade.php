@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-link-underline active inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-base font-medium leading-5 text-primary-dark focus:outline-none transition-all duration-300 ease-in-out hover:scale-110 hover:translate-y-[-2px]'
            : 'nav-link-underline inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-base font-medium leading-5 text-primary-dark hover:text-primary-dark hover:scale-110 hover:translate-y-[-2px] focus:outline-none transition-all duration-300 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
