<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-primary-dark border border-transparent rounded-md font-semibold text-xs text-primary-light uppercase tracking-widest hover:bg-primary-gray focus:bg-primary-gray active:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-dark focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
