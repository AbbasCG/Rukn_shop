{{-- Read-only star rating (0–5, fractions shown as a partly filled star). --}}
@props(['rating' => 0, 'size' => 'w-5 h-5'])
@php($rating = max(0, min(5, (float) $rating)))
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5']) }} dir="ltr" role="img"
      aria-label="{{ __('reviews.product.average', ['rating' => number_format($rating, 1)]) }}">
    @for ($i = 1; $i <= 5; $i++)
        <span class="relative inline-block {{ $size }}" aria-hidden="true">
            <svg class="absolute inset-0 {{ $size }} text-neutral-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
            <span class="absolute inset-0 overflow-hidden" style="width: {{ max(0, min(1, $rating - $i + 1)) * 100 }}%">
                <svg class="{{ $size }} text-warning" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
            </span>
        </span>
    @endfor
</span>
