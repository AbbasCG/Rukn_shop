<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeGuideRow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeGuideTest extends TestCase
{
    use RefreshDatabase;

    private function abaya(string $system = 'lengths', array $sizes = ['54', '56']): Product
    {
        $product = Product::factory()->for(Category::factory())->create([
            'name' => 'Black Abaya', 'price' => 80, 'stock' => 2, 'is_active' => true,
            'size_system' => $system, 'sizes' => $sizes, 'colors' => null,
        ]);
        foreach ($sizes as $size) {
            ProductVariant::create(['product_id' => $product->id, 'size' => $size, 'stock' => 1]);
        }

        return $product;
    }

    public function test_starts_with_the_standard_abaya_chart(): void
    {
        $rows = SizeGuideRow::ordered();

        $this->assertSame(['50', '52', '54', '56', '58', '60', '62'], $rows->pluck('size')->all());
        $this->assertSame(137, $rows->firstWhere('size', '54')->lengthCm());          // 54 × 2.54
        $this->assertSame([157, 163], [$rows->firstWhere('size', '54')->height_min, $rows->firstWhere('size', '54')->height_max]);
    }

    public function test_product_page_offers_the_guide_for_abaya_lengths(): void
    {
        $product = $this->abaya();

        $this->get('/en/products/' . $product->slug)->assertOk()
            ->assertSee('Size guide')->assertSee('Abaya size guide')
            ->assertSee('137 cm')->assertSee('157–163 cm')
            ->assertSee('How to measure');

        $this->get('/ar/products/' . $product->slug)->assertSee('دليل مقاسات العبايات')->assertSee('طريقة القياس');
        $this->get('/nl/products/' . $product->slug)->assertSee('Maattabel abaya');
    }

    public function test_not_shown_for_other_products_or_an_empty_guide(): void
    {
        $letters = $this->abaya('letters', ['S', 'M']);
        $this->get('/en/products/' . $letters->slug)->assertDontSee('Abaya size guide');

        $lengths = $this->abaya();
        SizeGuideRow::query()->delete();
        $this->get('/en/products/' . $lengths->slug)->assertOk()->assertDontSee('Abaya size guide');
    }

    public function test_admin_edits_the_table(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/en/admin/size-guide')->assertOk()->assertSee('Size guide');

        $this->actingAs($admin)->put('/en/admin/size-guide', ['rows' => [
            ['size' => '55', 'height_min' => '158', 'height_max' => '162'],
            ['size' => '57', 'height_min' => '163', 'height_max' => '167'],
        ]])->assertRedirect()->assertSessionHas('success', 'Size guide saved.');

        $this->assertSame(['55', '57'], SizeGuideRow::ordered()->pluck('size')->all());

        $product = $this->abaya('lengths', ['55']);
        $this->get('/en/products/' . $product->slug)->assertSee('158–162 cm')->assertDontSee('157–163 cm');
    }

    public function test_admin_validation_and_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put('/en/admin/size-guide', ['rows' => [
            ['size' => '54', 'height_min' => '170', 'height_max' => '160'],
        ]])->assertSessionHasErrors('rows.0.height_max');
        $this->actingAs($admin)->put('/en/admin/size-guide', ['rows' => [
            ['size' => '54', 'height_min' => '150', 'height_max' => '160'],
            ['size' => '54', 'height_min' => '160', 'height_max' => '170'],
        ]])->assertSessionHasErrors('rows.0.size');
        $this->assertSame(7, SizeGuideRow::count());                      // unchanged

        $this->actingAs(User::factory()->create())->put('/en/admin/size-guide', ['rows' => []])->assertForbidden();
        $this->assertSame(7, SizeGuideRow::count());
    }
}
