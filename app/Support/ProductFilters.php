<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Size and colour filters for the shop. Options come from the active
 * products' sizes/colours; colours are grouped by their name in the page
 * language (every "Black" together, whatever product it belongs to).
 * A product matches when it has a size/colour combination in stock.
 */
class ProductFilters
{
    /** Sizes that exist on active products, in a natural order. */
    public static function sizeOptions(): Collection
    {
        $sizes = ProductVariant::query()
            ->whereNotNull('size')
            ->whereHas('product', fn (Builder $q) => $q->where('is_active', true))
            ->distinct()
            ->pluck('size')
            ->map(fn ($s) => (string) $s)
            ->unique();

        $known = array_merge(Product::SIZE_LENGTHS, Product::SIZE_LETTERS);

        return $sizes->sortBy(function ($size) use ($known) {
            $index = array_search($size, $known, true);

            return $index === false ? '1-' . Str::lower($size) : sprintf('0-%03d', $index);
        }, SORT_NATURAL)->values();
    }

    /**
     * Colours on active products, grouped by name in the given language:
     * [value => ['name' => ..., 'hex' => ..., 'pairs' => [[product_id, color_key], ...]]]
     */
    public static function colorOptions(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();
        $options = [];

        Product::query()
            ->where('is_active', true)
            ->whereNotNull('colors')
            ->get(['id', 'colors'])
            ->each(function (Product $product) use ($locale, &$options) {
                foreach ($product->colors ?? [] as $color) {
                    $name = trim($product->colorName($color['key'], $locale));
                    if ($name === '') {
                        continue;
                    }
                    $value = Str::lower($name);
                    $options[$value] ??= ['name' => $name, 'hex' => $color['hex'] ?? '#ffffff', 'pairs' => []];
                    $options[$value]['pairs'][] = [$product->id, $color['key']];
                }
            });

        return collect($options)->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }

    /**
     * Keep products with a combination in stock in one of $sizes and one of
     * $colors (both optional).
     */
    public static function apply(Builder $query, array $sizes, array $colors, ?string $locale = null): Builder
    {
        $sizes = array_values(array_filter(array_map('strval', $sizes), fn ($s) => $s !== ''));
        $colors = array_values(array_filter(array_map(fn ($c) => Str::lower(trim((string) $c)), $colors), fn ($c) => $c !== ''));

        if (! $sizes && ! $colors) {
            return $query;
        }

        $pairs = $colors
            ? self::colorOptions($locale)->only($colors)->flatMap(fn ($option) => $option['pairs'])->all()
            : [];

        if ($colors && ! $pairs) {
            return $query->whereRaw('1 = 0');   // an unknown colour matches nothing
        }

        return $query->whereHas('variants', function (Builder $variant) use ($sizes, $pairs) {
            $variant->where('stock', '>', 0);
            if ($sizes) {
                $variant->whereIn('size', $sizes);
            }
            if ($pairs) {
                $variant->where(function (Builder $q) use ($pairs) {
                    foreach ($pairs as [$productId, $colorKey]) {
                        $q->orWhere(fn (Builder $p) => $p->where('product_variants.product_id', $productId)->where('color_key', $colorKey));
                    }
                });
            }
        });
    }
}
