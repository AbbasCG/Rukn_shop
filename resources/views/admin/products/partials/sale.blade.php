{{--
    Price reduction (product create/edit).
    Submits: sale_type (''|percent|price), sale_value, sale_start, sale_end (Y-m-d, optional).
    Expects optional $product.
--}}
@php
    $product = $product ?? null;
    $type = old('sale_type', $product?->sale_type ?? '');
    $start = old('sale_start', $product?->saleFirstDay()?->toDateString());
    $end = old('sale_end', $product?->saleLastDay()?->toDateString());
    $warnings = $product ? $product->saleReferenceWarnings() : [];
@endphp

<fieldset class="space-y-4 rounded-xl border border-primary-gray p-6" x-data="{ type: @js($type) }" x-init="$watch('type', t => $dispatch('sale-type', t))">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <legend class="text-lg font-semibold text-primary-dark">{{ __('admin.sale.title') }}</legend>
            <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.sale.help') }}</p>
        </div>
        @if ($product?->saleIsRunning())
            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">{{ __('admin.sale.status_running') }}</span>
        @elseif ($product?->saleIsScheduled())
            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ __('admin.sale.status_scheduled', ['date' => $product->saleFirstDay()->translatedFormat('j F Y')]) }}</span>
        @elseif ($product && $product->sale_type && $product->sale_ends_at?->isPast())
            <span class="rounded-full bg-primary-gray px-3 py-1 text-xs font-semibold text-primary-dark/70">{{ __('admin.sale.status_ended') }}</span>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach (['' => 'none', 'percent' => 'percent', 'price' => 'price'] as $value => $label)
            <label class="flex items-start gap-3 rounded-lg border border-primary-gray p-3 cursor-pointer hover:border-primary-dark/40"
                   :class="type === @js($value) ? 'border-primary-dark bg-primary-50' : ''">
                <input type="radio" name="sale_type" value="{{ $value }}" x-model="type" class="mt-1">
                <span>
                    <span class="block text-sm font-medium text-primary-dark">{{ __('admin.sale.type_' . $label) }}</span>
                    <span class="block text-xs text-primary-dark/60">{{ __('admin.sale.type_' . $label . '_desc') }}</span>
                </span>
            </label>
        @endforeach
    </div>
    @error('sale_type')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" x-show="type !== ''" x-cloak>
        <div>
            <label for="sale_value" class="block text-sm font-medium text-primary-dark mb-2">
                <span x-show="type === 'percent'">{{ __('admin.sale.value_percent') }}</span>
                <span x-show="type === 'price'">{{ __('admin.sale.value_price') }}</span>
            </label>
            <input id="sale_value" type="number" name="sale_value" step="0.01" min="0.01"
                   :max="type === 'percent' ? 99 : null" :required="type !== ''"
                   value="{{ old('sale_value', $product?->sale_value) }}"
                   class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark">
            @error('sale_value')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="sale_start" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.sale.start') }}</label>
            <input id="sale_start" type="date" name="sale_start" value="{{ $start }}"
                   class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark">
            @error('sale_start')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="sale_end" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.sale.end') }}</label>
            <input id="sale_end" type="date" name="sale_end" value="{{ $end }}"
                   class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark">
            @error('sale_end')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <p class="sm:col-span-3 text-xs text-primary-dark/60">{{ __('admin.sale.dates_hint') }}</p>
    </div>

    {{-- Dutch/EU price rules: the crossed-out price must be the lowest of the 30 days before. --}}
    <div class="rounded-lg bg-primary-50 px-4 py-3 text-xs leading-relaxed text-primary-dark/80" x-show="type !== ''" x-cloak>
        {{ __('admin.sale.rule') }}
    </div>
    @foreach ($warnings as $warning)
        <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" x-show="type !== ''">
            ⚠ {{ __('admin.sale.warning_' . $warning['key'], ['date' => $warning['date']->copy()->tz(\App\Models\Product::SALE_TIMEZONE)->translatedFormat('j F Y')]) }}
        </p>
    @endforeach
</fieldset>
