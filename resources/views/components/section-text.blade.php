@props(["class" => ""])
@php
    $rtl = app()->getLocale() === 'ar';
@endphp
<p {{ $attributes->merge(["class" => "text-base leading-relaxed text-neutral-700 " . ($rtl ? 'text-right' : 'text-left') . " {$class}"]) }}>
    {{ $slot }}
</p>
