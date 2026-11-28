<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use XMLWriter;

/**
 * Product feed (RSS 2.0 with the Google "g:" fields) for Google Merchant
 * Center and Meta Commerce Manager (Instagram / Facebook shop). Both read
 * this format. One feed per shop language.
 *
 * Products sold in sizes/colours become one item per size/colour, grouped
 * with item_group_id. Hidden products and products without a photo are
 * left out (both platforms reject items without an image).
 */
class ProductFeed
{
    public static function xml(string $locale): string
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            return self::build($locale);
        } finally {
            app()->setLocale($previous);
        }
    }

    /** Products that go into the feed, and the ones skipped for lack of a photo. */
    public static function products(): array
    {
        $all = Product::where('is_active', true)->with(['category', 'images', 'variants'])->orderBy('id')->get();

        return [
            'included' => $all->filter(fn (Product $p) => $p->imageUrls() !== [])->values(),
            'without_photo' => $all->filter(fn (Product $p) => $p->imageUrls() === [])->values(),
        ];
    }

    private static function build(string $locale): string
    {
        $business = (string) (config('shop.business.name') ?: config('app.name'));

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $xml->startElement('channel');
        $xml->writeElement('title', $business);
        $xml->writeElement('link', route('home', ['locale' => $locale]));
        $xml->writeElement('description', __('feeds.channel_description', ['shop' => $business]));

        foreach (self::products()['included'] as $product) {
            if ($product->hasVariants()) {
                foreach ($product->variants as $variant) {
                    self::item($xml, $product, $variant->setRelation('product', $product), $locale, $business);
                }
            } else {
                self::item($xml, $product, null, $locale, $business);
            }
        }

        $xml->endElement();   // channel
        $xml->endElement();   // rss
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private static function item(XMLWriter $xml, Product $product, ?ProductVariant $variant, string $locale, string $business): void
    {
        $images = $product->imageUrls();
        $regular = $variant ? $variant->regularPrice() : (float) $product->price;
        $current = $variant ? $variant->effectivePrice() : $product->currentPrice();
        $stock = $variant ? (int) $variant->stock : (int) $product->stock;
        $label = $variant?->label($locale);
        $description = trim(strip_tags((string) ($product->long_description ?: $product->short_description)));

        $xml->startElement('item');
        $g = fn (string $name, $value) => $value === null || $value === '' ? null : $xml->writeElement('g:' . $name, (string) $value);

        $g('id', 'rukn-' . $product->id . ($variant ? '-' . $variant->id : ''));
        if ($variant) {
            $g('item_group_id', 'rukn-' . $product->id);
        }
        $g('title', Str::limit($product->name . ($label ? ' - ' . $label : ''), 150, ''));
        $g('description', Str::limit($description !== '' ? $description : $product->name, 5000, ''));
        $g('link', route('products.show', ['locale' => $locale, 'product' => $product]));
        $g('image_link', $images[0]);
        foreach (array_slice($images, 1, 10) as $extra) {
            $g('additional_image_link', $extra);
        }
        $g('availability', $stock > 0 ? 'in_stock' : 'out_of_stock');
        $g('price', self::money($regular));
        if ($current < $regular) {
            $g('sale_price', self::money($current));
            if ($product->sale_starts_at || $product->sale_ends_at) {
                $g('sale_price_effective_date', ($product->sale_starts_at ?? now())->toIso8601String() . '/' . ($product->sale_ends_at ?? now()->addYear())->toIso8601String());
            }
        }
        $g('brand', $business);
        $g('condition', 'new');
        $g('identifier_exists', 'no');   // no barcodes (GTIN) for own products
        $g('product_type', $product->category?->display_name);
        $g('size', $variant?->size);
        if ($variant && $variant->color_key !== null) {
            $g('color', $product->colorName($variant->color_key, $locale));
        }

        // Shipping to the Netherlands as in the shop (free from the threshold).
        $xml->startElement('g:shipping');
        $xml->writeElement('g:country', 'NL');
        $xml->writeElement('g:price', self::money(Shipping::cost($current)));
        $xml->endElement();

        $xml->endElement();   // item
    }

    private static function money(float $amount): string
    {
        return number_format($amount, 2, '.', '') . ' EUR';
    }
}
