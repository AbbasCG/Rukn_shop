@props(["class" => ""])
@php
@endphp
<p {{ $attributes->merge(["class" => "text-base leading-relaxed text-neutral-700 " . 'text-start' . " {$class}"]) }}>
    {{ $slot }}
</p>
