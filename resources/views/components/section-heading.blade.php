@props(["level" => 2, "class" => ""])
@php
    $tag = 'h' . $level;
    $sizeClass = $level === 1 ? 'text-3xl' : 'text-2xl';
@endphp
<{{ $tag }} {{ $attributes->merge(["class" => "font-bold tracking-tight {$sizeClass} " . 'text-start' . " {$class}"]) }}>
    {{ $slot }}
</{{ $tag }}>
