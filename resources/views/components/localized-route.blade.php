<x-dynamic-component
    component="link"
    :href="r($routeName, $parameters ?? [])"
    {{ $attributes }}>
    {{ $slot }}
</x-dynamic-component>
