<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFeedTest extends TestCase
{
    use RefreshDatabase;

    private Product $abaya;

    private Product $mabkhara;

    private ProductVariant $v54;

    private ProductVariant $v56;

    protected function setUp(): void
    {
        parent::setUp();
        config(['shop.business.name' => 'Rukn', 'shop.shipping.free_threshold' => 50, 'shop.shipping.flat_rate' => 4.95]);
        $category = Category::factory()->create(['name' => 'Abaya', 'name_translations' => ['nl' => "Abaya's", 'en' => 'Abayas', 'ar' => 'عبايات']]);

        $this->abaya = Product::factory()->for($category)->create([
            'name' => 'Black Abaya', 'price' => 80, 'stock' => 2, 'is_active' => true, 'image_url' => null,
            'long_description' => '<p>Soft <b>crepe</b> & lining</p>',
            'size_system' => 'lengths', 'sizes' => ['54', '56'],
            'colors' => [['key' => 'k1', 'hex' => '#000000', 'names' => ['nl' => 'Zwart', 'en' => 'Black', 'ar' => 'أسود']]],
        ]);
        $this->v54 = ProductVariant::create(['product_id' => $this->abaya->id, 'size' => '54', 'color_key' => 'k1', 'stock' => 2]);
        $this->v56 = ProductVariant::create(['product_id' => $this->abaya->id, 'size' => '56', 'color_key' => 'k1', 'stock' => 0]);
        ProductImage::create(['product_id' => $this->abaya->id, 'path' => 'products/abaya-2.jpg', 'is_primary' => false]);
        ProductImage::create(['product_id' => $this->abaya->id, 'path' => 'products/abaya-1.jpg', 'is_primary' => true]);

        $this->mabkhara = Product::factory()->for($category)->create([
            'name' => 'Gold Mabkhara', 'price' => 30, 'stock' => 4, 'is_active' => true,
            'image_url' => 'https://cdn.example.com/mabkhara.jpg', 'sizes' => null, 'colors' => null, 'size_system' => null,
        ]);

        Product::factory()->for($category)->create(['name' => 'No Photo Abaya', 'is_active' => true, 'image_url' => null]);
        Product::factory()->for($category)->create(['name' => 'Hidden Abaya', 'is_active' => false, 'image_url' => 'https://cdn.example.com/x.jpg']);
    }

    private function feed(string $locale = 'nl'): \SimpleXMLElement
    {
        $response = $this->get("/{$locale}/feeds/products.xml")->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        return simplexml_load_string($response->getContent());
    }

    /** @return array<string, \SimpleXMLElement> items by g:id */
    private function items(\SimpleXMLElement $xml): array
    {
        $items = [];
        foreach ($xml->channel->item as $item) {
            $g = $item->children('http://base.google.com/ns/1.0');
            $items[(string) $g->id] = $g;
        }

        return $items;
    }

    public function test_one_item_per_size_and_colour_grouped_together(): void
    {
        $items = $this->items($this->feed('nl'));
        $abaya = $this->abaya->id;

        $this->assertSame(["rukn-{$abaya}-{$this->v54->id}", "rukn-{$abaya}-{$this->v56->id}", "rukn-{$this->mabkhara->id}"], array_keys($items));
        $first = $items["rukn-{$abaya}-{$this->v54->id}"];
        $this->assertSame("rukn-{$abaya}", (string) $first->item_group_id);
        $this->assertSame('Black Abaya - 54 · Zwart', (string) $first->title);
        $this->assertSame('54', (string) $first->size);
        $this->assertSame('Zwart', (string) $first->color);
        $this->assertSame('in_stock', (string) $first->availability);
        $this->assertSame('out_of_stock', (string) $items["rukn-{$abaya}-{$this->v56->id}"]->availability);
        $this->assertSame("Abaya's", (string) $first->product_type);
        $this->assertSame('Soft crepe & lining', (string) $first->description);    // no HTML
        $this->assertSame(route('products.show', ['locale' => 'nl', 'product' => $this->abaya]), (string) $first->link);
        $this->assertStringEndsWith('abaya-1.jpg', (string) $first->image_link);   // primary first
        $this->assertStringEndsWith('abaya-2.jpg', (string) $first->additional_image_link);
        $this->assertSame('Rukn', (string) $first->brand);
        $this->assertSame('new', (string) $first->condition);
        $this->assertSame('no', (string) $first->identifier_exists);
    }

    public function test_prices_shipping_and_sales(): void
    {
        $this->mabkhara->forceFill(['sale_type' => 'percent', 'sale_value' => 20, 'sale_ends_at' => now()->addWeek()])->save();
        $items = $this->items($this->feed());

        $abaya = $items["rukn-{$this->abaya->id}-{$this->v54->id}"];
        $this->assertSame('80.00 EUR', (string) $abaya->price);
        $this->assertSame('0.00 EUR', (string) $abaya->shipping->price);     // above the free threshold
        $this->assertSame('NL', (string) $abaya->shipping->country);

        $mabkhara = $items["rukn-{$this->mabkhara->id}"];
        $this->assertSame('30.00 EUR', (string) $mabkhara->price);
        $this->assertSame('24.00 EUR', (string) $mabkhara->sale_price);
        $this->assertStringContainsString('/', (string) $mabkhara->sale_price_effective_date);
        $this->assertSame('4.95 EUR', (string) $mabkhara->shipping->price);
        $this->assertSame('', (string) $mabkhara->item_group_id);
    }

    public function test_hidden_and_photoless_products_are_left_out(): void
    {
        $titles = array_map(fn ($g) => (string) $g->title, $this->items($this->feed()));

        $this->assertNotContains('Hidden Abaya', $titles);
        $this->assertNotContains('No Photo Abaya', $titles);
    }

    public function test_each_language_has_its_own_feed(): void
    {
        $ar = $this->items($this->feed('ar'))["rukn-{$this->abaya->id}-{$this->v54->id}"];
        $this->assertSame('أسود', (string) $ar->color);
        $this->assertSame('عبايات', (string) $ar->product_type);
        $this->assertStringContainsString('/ar/products/', (string) $ar->link);

        $this->assertSame('Black', (string) $this->items($this->feed('en'))["rukn-{$this->abaya->id}-{$this->v54->id}"]->color);
    }

    public function test_admin_page_lists_links_and_photoless_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/en/admin/feeds')->assertOk()
            ->assertSee(route('feeds.products', ['locale' => 'nl']))
            ->assertSee(route('feeds.products', ['locale' => 'ar']))
            ->assertSee('2 products are in the feed')
            ->assertSee('No Photo Abaya')
            ->assertSee('Google Merchant Center');
        $this->actingAs($admin)->get('/ar/admin/feeds')->assertSee('كتالوج المنتجات');
        $this->actingAs(User::factory()->create())->get('/en/admin/feeds')->assertForbidden();
    }
}
