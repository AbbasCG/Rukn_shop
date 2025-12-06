@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-dark text-start text-base font-medium text-primary-dark bg-primary-light focus:outline-none focus:text-primary-dark focus:bg-primary-gray focus:border-primary-dark transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-primary-gray hover:text-primary-dark hover:bg-primary-light hover:border-primary-gray focus:outline-none focus:text-primary-dark focus:bg-primary-light focus:border-primary-gray transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
