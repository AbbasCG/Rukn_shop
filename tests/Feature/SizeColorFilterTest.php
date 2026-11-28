<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\ProductFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeColorFilterTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();

        // Black (k1) and white (k2): 54 black in stock, 56 black sold out, 54 white in stock.
        $a = $this->product('Abaya Noor', [
            ['key' => 'k1', 'hex' => '#000000', 'names' => ['nl' => 'Zwart', 'en' => 'Black', 'ar' => 'أسود']],
            ['key' => 'k2', 'hex' => '#ffffff', 'names' => ['nl' => 'Wit', 'en' => 'White', 'ar' => 'أبيض']],
        ]);
        $this->variant($a, '54', 'k1', 2);
        $this->variant($a, '56', 'k1', 0);
        $this->variant($a, '54', 'k2', 1);

        // Another product's black has a different key but the same name: grouped together.
        $b = $this->product('Abaya Layla', [
            ['key' => 'zz9', 'hex' => '#111111', 'names' => ['nl' => 'Zwart', 'en' => 'Black', 'ar' => 'أسود']],
        ]);
        $this->variant($b, '56', 'zz9', 3);

        // Sizes only (no colours).
        $c = $this->product('Abaya Sara', null);
        $this->variant($c, '58', null, 1);

        // No sizes or colours at all.
        $this->product('Gold Mabkhara', null);

        // Hidden products never count.
        $hidden = $this->product('Hidden Abaya', [['key' => 'r', 'hex' => '#ff0000', 'names' => ['en' => 'Red']]], false);
        $this->variant($hidden, '60', 'r', 5);
    }

    private function product(string $name, ?array $colors, bool $active = true): Product
    {
        return Product::factory()->for($this->category)->create([
            'name' => $name, 'price' => 50, 'stock' => 5, 'is_active' => $active, 'colors' => $colors,
            'sizes' => null, 'size_system' => null,
        ]);
    }

    private function variant(Product $product, ?string $size, ?string $color, int $stock): void
    {
        ProductVariant::create(['product_id' => $product->id, 'size' => $size, 'color_key' => $color, 'stock' => $stock]);
    }

    private function names(string $query): array
    {
        return $this->get('/en/products?' . $query)->assertOk()->viewData('products')->pluck('name')->sort()->values()->all();
    }

    public function test_options(): void
    {
        $this->assertSame(['54', '56', '58'], ProductFilters::sizeOptions()->all());

        $colors = ProductFilters::colorOptions('en');
        $this->assertSame(['black', 'white'], $colors->keys()->all());
        $this->assertCount(2, $colors['black']['pairs']);          // two products, two different keys

        $this->assertSame(['أبيض', 'أسود'], ProductFilters::colorOptions('ar')->pluck('name')->sort()->values()->all());
    }

    public function test_filter_by_size_only_counts_stock(): void
    {
        $this->assertSame(['Abaya Noor'], $this->names('sizes[]=54'));
        $this->assertSame(['Abaya Layla'], $this->names('sizes[]=56'));           // Noor's 56 is sold out
        $this->assertSame(['Abaya Layla', 'Abaya Sara'], $this->names('sizes[]=56&sizes[]=58'));
    }

    public function test_filter_by_colour_groups_names_across_products(): void
    {
        $this->assertSame(['Abaya Layla', 'Abaya Noor'], $this->names('colors[]=black'));
        $this->assertSame(['Abaya Noor'], $this->names('colors[]=White'));          // case does not matter
        $this->assertSame([], $this->names('colors[]=purple'));
    }

    public function test_size_and_colour_must_be_the_same_item(): void
    {
        $this->assertSame(['Abaya Noor'], $this->names('sizes[]=54&colors[]=white'));
        $this->assertSame(['Abaya Layla'], $this->names('sizes[]=56&colors[]=black'));   // not Noor: its black 56 is sold out
        $this->assertSame([], $this->names('sizes[]=58&colors[]=black'));
    }

    public function test_combines_with_other_filters_and_keeps_them(): void
    {
        $this->assertSame(['Abaya Noor'], $this->names('sizes[]=54&q=noor'));

        $this->get('/en/products?sizes[]=54&colors[]=black&sort=name')->assertOk()
            ->assertSee('name="sizes[]" value="54" class="peer sr-only" checked', false)
            ->assertSee('<input type="hidden" name="colors[]" value="black">', false)
            ->assertSee('Clear all filters');
    }

    public function test_panel_shows_the_options_in_the_page_language(): void
    {
        $this->get('/ar/products')->assertSee('المقاس')->assertSee('اللون')->assertSee('أسود')->assertDontSee('Red');
        $this->get('/nl/products')->assertSee('Maat')->assertSee('Zwart');
        $this->assertSame(['Abaya Layla', 'Abaya Noor'], $this->get('/ar/products?colors[]=أسود')->viewData('products')->pluck('name')->sort()->values()->all());
    }

    public function test_bad_input_is_ignored_safely(): void
    {
        $this->get('/en/products?sizes=54')->assertOk();                 // a single value works too
        $this->get('/en/products?sizes[][]=x&colors[a]=1')->assertOk();
    }
}
