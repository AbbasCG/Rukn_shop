@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-dark text-start text-base font-medium text-primary-dark bg-primary-light focus:outline-none focus:text-primary-dark focus:bg-primary-gray focus:border-primary-dark transition duration-300 ease-in-out transform hover:translate-x-1 hover:scale-105'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-primary-gray hover:text-primary-dark hover:bg-primary-light/80 hover:border-primary-gray focus:outline-none focus:text-primary-dark focus:bg-primary-light focus:border-primary-gray transition duration-300 ease-in-out transform hover:translate-x-1 hover:scale-105';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
