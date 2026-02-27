@props(["level" => 2, "class" => ""])
@php
    $rtl = app()->getLocale() === 'ar';
    $tag = 'h' . $level;
    $sizeClass = $level === 1 ? 'text-3xl' : 'text-2xl';
@endphp
<{{ $tag }} {{ $attributes->merge(["class" => "font-bold tracking-tight {$sizeClass} " . ($rtl ? 'text-right' : 'text-left') . " {$class}"]) }}>
    {{ $slot }}
</{{ $tag }}>
