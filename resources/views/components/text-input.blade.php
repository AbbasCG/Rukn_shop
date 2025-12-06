@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-primary-gray bg-primary-light text-primary-dark focus:border-primary-dark focus:ring-primary-dark rounded-md shadow-sm']) }}>
