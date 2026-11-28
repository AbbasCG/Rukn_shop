{{-- Customers waiting for an email when this product (or a size/colour) is back. --}}
@php
    $waiting = $product->stockNotifications()->whereNull('notified_at')->with('variant')->get()
        ->groupBy(fn ($n) => $n->variant_id ?? 0)
        ->map(fn ($group) => [
            'label' => $group->first()->variant?->setRelation('product', $product)->label() ?: null,
            'count' => $group->count(),
        ])
        ->sortByDesc('count')->values();
@endphp
@if ($waiting->isNotEmpty())
    <div class="rounded-xl border border-primary-gray bg-primary-50 p-4 text-sm text-primary-dark">
        <p class="font-semibold">🔔 {{ trans_choice('admin.stock_alerts.waiting', $waiting->sum('count'), ['count' => $waiting->sum('count')]) }}</p>
        <p class="mt-1 text-primary-dark/70">{{ __('admin.stock_alerts.explain') }}</p>
        @if ($waiting->contains(fn ($w) => $w['label']))
            <ul class="mt-2 flex flex-wrap gap-2">
                @foreach ($waiting as $w)
                    <li class="rounded-lg bg-white px-3 py-1 border border-primary-gray"><bdi>{{ $w['label'] ?? '—' }}</bdi>: {{ $w['count'] }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
