@props([
    'href' => null,
    'active' => false,
    'disabled' => false,
])

@php
    $base = 'group flex items-center gap-3 px-3 py-2 rounded-md transition-colors transition-transform duration-200';
    $state = $disabled
        ? 'opacity-50 cursor-not-allowed'
        : ($active ? 'bg-primary-gray text-primary-dark shadow-sm' : 'text-primary-dark/80 hover:text-primary-dark hover:bg-primary-gray/70 hover:-translate-x-0.5');
@endphp

@if($disabled)
    <div class="{{ $base }} {{ $state }}">
        <div class="shrink-0">
            {{ $icon ?? '' }}
        </div>
        <div class="flex-1 min-w-0 truncate">
            {{ $slot }}
            <span class="ml-1 text-xs text-primary-dark/50">(soon)</span>
        </div>
    </div>
@else
    <a href="{{ $href }}" class="{{ $base }} {{ $state }}">
        <div class="shrink-0">
            {{ $icon ?? '' }}
        </div>
        <div class="flex-1 min-w-0 truncate">
            {{ $slot }}
        </div>
        <span class="ml-auto w-1 h-6 rounded-full bg-primary-dark/10 group-hover:bg-primary-dark/40 {{ $active ? 'bg-primary-dark/60' : '' }}"></span>
    </a>
@endif
