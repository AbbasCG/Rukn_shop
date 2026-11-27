{{--
    Sizes & colours editor (product create/edit).
    Submits: size_system, sizes[], colors[i][key|hex|names][nl|en|ar],
             variants[i][size|color_key|stock|price]
    Expects optional $product.
--}}
@php
    $product = $product ?? null;
    $old = fn ($key, $default) => old($key) !== null ? old($key) : $default;

    $initial = [
        'sizeSystem' => $old('size_system', $product?->size_system ?? ''),
        'sizes' => array_values($old('sizes', $product?->sizes ?? [])),
        'colors' => array_values($old('colors', $product?->colors ?? [])),
        'variants' => array_values($old('variants', $product
            ? $product->variants->map(fn ($v) => [
                'size' => $v->size, 'color_key' => $v->color_key,
                'stock' => $v->stock, 'price' => $v->price,
            ])->all()
            : [])),
    ];
    $lists = ['lengths' => \App\Models\Product::SIZE_LENGTHS, 'letters' => \App\Models\Product::SIZE_LETTERS];
    $systems = [
        '' => ['admin.variants.size_none', 'admin.variants.size_none_desc'],
        'lengths' => ['admin.variants.size_lengths', 'admin.variants.size_lengths_desc'],
        'letters' => ['admin.variants.size_letters', 'admin.variants.size_letters_desc'],
        'custom' => ['admin.variants.size_custom', 'admin.variants.size_custom_desc'],
    ];
    $languages = ['nl' => 'Nederlands', 'en' => 'English', 'ar' => 'العربية'];
@endphp

<fieldset
    class="space-y-6 rounded-xl border border-primary-gray p-6"
    x-data="productVariants(@js($initial), @js($lists))"
>
    <div>
        <legend class="text-lg font-semibold text-primary-dark">{{ __('admin.variants.title') }}</legend>
        <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.variants.help') }}</p>
    </div>

    {{-- 1. Size system --}}
    <div class="space-y-2">
        <p class="text-sm font-medium text-primary-dark">{{ __('admin.variants.size_system') }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach ($systems as $value => [$label, $desc])
                <label class="flex items-start gap-3 rounded-lg border border-primary-gray p-3 cursor-pointer hover:border-primary-dark/40"
                       :class="sizeSystem === @js($value) ? 'border-primary-dark bg-primary-50' : ''">
                    <input type="radio" name="size_system" value="{{ $value }}" x-model="sizeSystem" class="mt-1">
                    <span>
                        <span class="block text-sm font-medium text-primary-dark">{{ __($label) }}</span>
                        <span class="block text-xs text-primary-dark/60">{{ __($desc) }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('size_system')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    </div>

    {{-- 2. Sizes --}}
    <div class="space-y-2" x-show="sizeSystem !== ''" x-cloak>
        <p class="text-sm font-medium text-primary-dark">{{ __('admin.variants.sizes') }}</p>
        <template x-if="sizeSystem === 'lengths' || sizeSystem === 'letters'">
            <div class="flex flex-wrap gap-2">
                <template x-for="size in lists[sizeSystem]" :key="size">
                    <label class="inline-flex items-center gap-2 rounded-lg border border-primary-gray px-3 py-1.5 text-sm cursor-pointer"
                           :class="sizes.includes(size) ? 'border-primary-dark bg-primary-50' : ''">
                        <input type="checkbox" :value="size" x-model="sizes">
                        <span x-text="size" dir="ltr"></span>
                    </label>
                </template>
            </div>
        </template>
        <template x-if="sizeSystem === 'custom'">
            <input type="text" x-model="customSizes" placeholder="{{ __('admin.variants.custom_sizes_placeholder') }}"
                   class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark">
        </template>
        <template x-for="size in activeSizes" :key="'s-' + size">
            <input type="hidden" name="sizes[]" :value="size">
        </template>
        @error('sizes')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        @error('sizes.*')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    </div>

    {{-- 3. Colours --}}
    <div class="space-y-3">
        <p class="text-sm font-medium text-primary-dark">{{ __('admin.variants.colors') }}</p>
        <template x-for="(color, index) in colors" :key="color.key">
            <div class="flex flex-wrap items-end gap-3 rounded-lg border border-primary-gray p-3">
                <input type="hidden" :name="`colors[${index}][key]`" :value="color.key">
                <label class="text-xs text-primary-dark/70">
                    {{ __('admin.variants.color_swatch') }}
                    <input type="color" :name="`colors[${index}][hex]`" x-model="color.hex" class="block mt-1 h-10 w-14 rounded border border-primary-gray">
                </label>
                @foreach ($languages as $code => $language)
                    <label class="flex-1 min-w-32 text-xs text-primary-dark/70">
                        {{ $language }}
                        <input type="text" :name="`colors[${index}][names][{{ $code }}]`" x-model="color.names.{{ $code }}"
                               lang="{{ $code }}" dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}" maxlength="50"
                               class="block w-full mt-1 px-3 py-2 rounded-lg border border-primary-gray bg-white text-sm text-primary-dark">
                    </label>
                @endforeach
                <button type="button" @click="removeColor(index)" class="px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50">
                    {{ __('admin.variants.remove') }}
                </button>
            </div>
        </template>
        <button type="button" @click="addColor()" class="px-4 py-2 rounded-lg border border-primary-gray text-sm text-primary-dark hover:bg-primary-gray">
            + {{ __('admin.variants.add_color') }}
        </button>
        @error('colors')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        @error('colors.*')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    </div>

    {{-- 4. Stock & price per variant --}}
    <div class="space-y-2">
        <p class="text-sm font-medium text-primary-dark">{{ __('admin.variants.table') }}</p>
        <p class="text-xs text-primary-dark/60" x-show="combos.length === 0">{{ __('admin.variants.empty') }}</p>
        <div class="overflow-x-auto" x-show="combos.length > 0" x-cloak>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-start text-primary-dark/70">
                        <th class="py-2 pe-4 text-start font-medium" x-show="activeSizes.length">{{ __('admin.variants.size') }}</th>
                        <th class="py-2 pe-4 text-start font-medium" x-show="colors.length">{{ __('admin.variants.color') }}</th>
                        <th class="py-2 pe-4 text-start font-medium">{{ __('admin.variants.stock') }}</th>
                        <th class="py-2 text-start font-medium">{{ __('admin.variants.price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(combo, index) in combos" :key="combo.id">
                        <tr class="border-t border-primary-gray">
                            <td class="py-2 pe-4" x-show="activeSizes.length"><span dir="ltr" x-text="combo.size"></span></td>
                            <td class="py-2 pe-4" x-show="colors.length">
                                <span class="inline-flex items-center gap-2" x-show="combo.color">
                                    <span class="h-4 w-4 rounded-full border border-primary-gray" :style="`background:${combo.color?.hex || '#ffffff'}`"></span>
                                    <span x-text="colorLabel(combo.color)"></span>
                                </span>
                            </td>
                            <td class="py-2 pe-4">
                                <input type="hidden" :name="`variants[${index}][size]`" :value="combo.size ?? ''">
                                <input type="hidden" :name="`variants[${index}][color_key]`" :value="combo.color?.key ?? ''">
                                <input type="number" min="0" step="1" required :name="`variants[${index}][stock]`"
                                       x-model="values[combo.id].stock" class="w-24 px-3 py-1.5 rounded-lg border border-primary-gray">
                            </td>
                            <td class="py-2">
                                <input type="number" min="0" step="0.01" :name="`variants[${index}][price]`"
                                       x-model="values[combo.id].price" class="w-28 px-3 py-1.5 rounded-lg border border-primary-gray">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="mt-2 text-xs text-primary-dark/60">{{ __('admin.variants.price_hint') }}</p>
            <p class="text-xs text-primary-dark/60">{{ __('admin.variants.stock_note') }}</p>
        </div>
        @error('variants')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        @error('variants.*')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    </div>
</fieldset>
