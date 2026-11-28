<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\ProductSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $abayas = Category::factory()->create(['name' => 'Abaya', 'name_translations' => ['nl' => "Abaya's", 'en' => 'Abayas', 'ar' => 'عبايات']]);
        $incense = Category::factory()->create(['name' => 'Bakhoor', 'name_translations' => ['nl' => 'Wierook', 'en' => 'Incense', 'ar' => 'بخور']]);

        $this->product('Black Crepe Abaya', $abayas, ['short_description' => 'Soft crepe for every day', 'colors' => [
            ['key' => 'k1', 'hex' => '#000000', 'names' => ['nl' => 'Zwart', 'en' => 'Black', 'ar' => 'أسود']],
        ]]);
        $this->product('Linen Kimono', Category::factory()->create(['name' => 'Kimono', 'name_translations' => null]), ['short_description' => 'Light summer linen']);
        $this->product('مبخرة ذهبية', $incense, ['short_description' => 'Elektrische mabkhara met USB']);
        $this->product('Hidden Abaya', $abayas, ['is_active' => false]);
    }

    private function product(string $name, Category $category, array $attributes = []): Product
    {
        return Product::factory()->for($category)->create($attributes + ['name' => $name, 'price' => 50, 'stock' => 5, 'is_active' => true]);
    }

    private function names(string $q, string $locale = 'en'): array
    {
        return $this->get("/{$locale}/products?q=" . urlencode($q))->assertOk()->viewData('products')->pluck('name')->all();
    }

    public function test_every_word_must_match_in_any_order(): void
    {
        $this->assertSame(['Black Crepe Abaya'], $this->names('abaya black'));
        $this->assertSame([], $this->names('abaya summer'));
        $this->assertSame(['Linen Kimono'], $this->names('summer linen'));
    }

    public function test_category_and_colour_names_in_every_language(): void
    {
        $this->assertSame(['مبخرة ذهبية'], $this->names('wierook'));            // category, Dutch
        $this->assertSame(['مبخرة ذهبية'], $this->names('بخور', 'ar'));
        $this->assertSame(['Black Crepe Abaya'], $this->names('zwart'));         // colour, Dutch
        $this->assertSame(['Black Crepe Abaya'], $this->names('اسود', 'ar'));    // without the hamza
    }

    public function test_arabic_letter_variants_and_harakat_are_ignored(): void
    {
        $this->assertSame(['مبخرة ذهبية'], $this->names('مبخره'));               // ة written as ه
        $this->assertSame(['مبخرة ذهبية'], $this->names('مِبْخَرَة'));            // with harakat
        $this->assertSame('ا ي ه', ProductSearch::normalize('أ ى ة'));
    }

    public function test_small_typos_are_forgiven_and_reported(): void
    {
        $response = $this->get('/en/products?q=abya')->assertOk();
        $this->assertSame(['Black Crepe Abaya'], $response->viewData('products')->pluck('name')->all());
        $response->assertSee('No exact match for “abya”. Showing results for:')->assertSee('abaya');

        $this->assertSame(['Linen Kimono'], $this->names('kimona'));
        $this->assertSame([], $this->names('xyzzy'));                            // too far from anything
    }

    public function test_best_match_first_unless_another_order_is_chosen(): void
    {
        $this->product('Crepe Hijab', Category::factory()->create(), ['short_description' => 'Matches crepe']);
        // A match at the start of the name ranks above one in the description.
        $this->assertSame('Crepe Hijab', $this->names('crepe')[0]);
        $names = $this->get('/en/products?q=crepe&sort=name')->viewData('products')->pluck('name')->all();
        $this->assertSame(['Black Crepe Abaya', 'Crepe Hijab'], $names);
    }

    public function test_suggestions_while_typing(): void
    {
        $this->getJson('/en/search/suggest?q=abay')->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'Black Crepe Abaya')
            ->assertJsonPath('products.0.price', '€50.00');

        $this->getJson('/en/search/suggest?q=kimona')->assertJsonPath('corrected', 'kimono');
        $this->getJson('/en/search/suggest?q=a')->assertJsonCount(0, 'products');
        $this->getJson('/en/search/suggest?q=hidden')->assertJsonCount(0, 'products');   // never hidden products
    }

    public function test_search_box_has_the_suggestion_list(): void
    {
        $this->get('/en/products')->assertSee('role="combobox"', false)->assertSee('See all results');
        $this->get('/ar/products')->assertSee('عرض كل النتائج');
    }
}
