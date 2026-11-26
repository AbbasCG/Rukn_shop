<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSlugUrlTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::factory()->for(Category::factory())->create(array_merge([
            'name' => 'Black Abaya', 'slug' => 'black-abaya', 'is_active' => true, 'stock' => 3,
        ], $attributes));
    }

    public function test_generated_product_urls_use_the_slug(): void
    {
        $product = $this->product();

        $this->assertSame(url('/nl/products/black-abaya'), route('products.show', ['locale' => 'nl', 'product' => $product]));
        $this->get('/nl/products/black-abaya')->assertOk()->assertSee('Black Abaya');
    }

    public function test_old_numeric_links_redirect_permanently_to_the_slug(): void
    {
        $product = $this->product();

        $this->get("/ar/products/{$product->id}?utm_source=whatsapp")
            ->assertStatus(301)
            ->assertRedirect(url('/ar/products/black-abaya') . '?utm_source=whatsapp');
    }

    public function test_upper_case_slugs_redirect_to_the_lower_case_url(): void
    {
        $this->product();

        $this->get('/en/products/Black-Abaya')->assertStatus(301)->assertRedirect(url('/en/products/black-abaya'));
    }

    public function test_unknown_products_are_404(): void
    {
        $this->get('/en/products/does-not-exist')->assertNotFound();
        $this->get('/en/products/99999')->assertNotFound();
    }

    public function test_unique_slugs_are_clean_and_numbered_on_clashes(): void
    {
        $this->assertSame('black-abaya', Product::uniqueSlug('Black Abaya'));

        $first = $this->product();
        $this->assertSame('black-abaya-2', Product::uniqueSlug('Black Abaya'));
        $this->assertSame('black-abaya', Product::uniqueSlug('Black Abaya', ignoreId: $first->id));
        $this->assertSame('product', Product::uniqueSlug('🙂'));
    }

    public function test_admin_slug_input_is_normalised_and_generated_when_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $base = [
            'category_id' => $category->id, 'price' => 49, 'stock' => 5,
            'short_description' => 'Soft crepe abaya.', 'is_active' => 1,
        ];

        $this->actingAs($admin)->post('/en/admin/products', $base + ['name' => 'Black Abaya', 'slug' => ''])->assertRedirect();
        $this->actingAs($admin)->post('/en/admin/products', $base + ['name' => 'Black Abaya', 'slug' => ''])->assertRedirect();
        $this->actingAs($admin)->post('/en/admin/products', $base + ['name' => 'Long one', 'slug' => '  Black Abaya LONG '])->assertRedirect();

        $this->assertSame(['black-abaya', 'black-abaya-2', 'black-abaya-long'], Product::orderBy('id')->pluck('slug')->all());

        // A typed slug that clashes after normalising is rejected.
        $this->actingAs($admin)->post('/en/admin/products', $base + ['name' => 'Other', 'slug' => 'BLACK ABAYA'])
            ->assertSessionHasErrors('slug');
    }

    public function test_admin_edit_and_update_work_with_slug_and_old_id_urls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();

        $this->actingAs($admin)->get('/en/admin/products/black-abaya/edit')->assertOk();
        $this->actingAs($admin)->get("/en/admin/products/{$product->id}/edit")->assertOk();
        $this->actingAs($admin)->get('/en/admin/products')->assertOk()
            ->assertSee(route('admin.products.edit', ['locale' => 'en', 'product' => $product]), false);

        $this->actingAs($admin)->patch('/en/admin/products/black-abaya', [
            'name' => 'Black Abaya', 'slug' => '', 'category_id' => $product->category_id,
            'price' => 55, 'stock' => 3, 'short_description' => 'Updated.', 'is_active' => 1,
        ])->assertRedirect();

        $product->refresh();
        $this->assertEquals(55, $product->price);
        $this->assertSame('black-abaya', $product->slug); // empty input keeps the existing slug
    }
}
