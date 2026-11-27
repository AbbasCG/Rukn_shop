<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSlugRedirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSlugRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $slug, string $name = 'Black Abaya'): Product
    {
        return Product::factory()->for(Category::factory())->create([
            'name' => $name, 'slug' => $slug, 'is_active' => true, 'stock' => 3,
        ]);
    }

    public function test_changing_a_slug_keeps_the_old_link_working(): void
    {
        $product = $this->product('black-abaya');

        $product->update(['slug' => 'classic-black-abaya']);

        $this->assertDatabaseHas('product_slug_redirects', ['old_slug' => 'black-abaya', 'product_id' => $product->id]);
        $this->get('/nl/products/black-abaya?utm_source=whatsapp')
            ->assertStatus(301)
            ->assertRedirect(url('/nl/products/classic-black-abaya') . '?utm_source=whatsapp');
        $this->get('/nl/products/classic-black-abaya')->assertOk();
    }

    public function test_every_old_slug_redirects_in_one_hop_to_the_current_one(): void
    {
        $product = $this->product('a');
        $product->update(['slug' => 'b']);
        $product->update(['slug' => 'c']);

        $this->get('/en/products/a')->assertStatus(301)->assertRedirect(url('/en/products/c'));
        $this->get('/en/products/b')->assertStatus(301)->assertRedirect(url('/en/products/c'));
    }

    public function test_changing_back_to_an_old_slug_does_not_redirect_to_itself(): void
    {
        $product = $this->product('a');
        $product->update(['slug' => 'b']);
        $product->update(['slug' => 'a']);

        $this->assertDatabaseMissing('product_slug_redirects', ['old_slug' => 'a']);
        $this->get('/en/products/a')->assertOk();
        $this->get('/en/products/b')->assertStatus(301)->assertRedirect(url('/en/products/a'));
    }

    public function test_a_product_can_take_over_a_slug_another_product_used_before(): void
    {
        $first = $this->product('summer-abaya', 'First');
        $first->update(['slug' => 'first-abaya']);

        $second = $this->product('summer-abaya', 'Second');

        $this->assertDatabaseMissing('product_slug_redirects', ['old_slug' => 'summer-abaya']);
        $this->get('/en/products/summer-abaya')->assertOk()->assertSee('Second');
        $this->assertSame('summer-abaya', $second->slug);
    }

    public function test_deleting_a_product_removes_its_redirects(): void
    {
        $product = $this->product('a');
        $product->update(['slug' => 'b']);

        $product->delete();

        $this->assertSame(0, ProductSlugRedirect::count());
        $this->get('/en/products/a')->assertNotFound();
    }

    public function test_slug_change_from_admin_is_recorded_and_shown_on_the_edit_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product('black-abaya');

        $this->actingAs($admin)->patch('/en/admin/products/black-abaya', [
            'name' => 'Black Abaya', 'slug' => 'Classic Black Abaya', 'category_id' => $product->category_id,
            'price' => 49, 'stock' => 3, 'short_description' => 'Soft.', 'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame('classic-black-abaya', $product->fresh()->slug);
        $this->get('/ar/products/black-abaya')->assertStatus(301)->assertRedirect(url('/ar/products/classic-black-abaya'));

        $this->actingAs($admin)->get('/en/admin/products/classic-black-abaya/edit')
            ->assertOk()
            ->assertSee('Old links that still redirect here:')
            ->assertSee('<code dir="ltr" class="px-1 rounded bg-primary-gray">black-abaya</code>', false);
    }

    public function test_saving_without_changing_the_slug_records_nothing(): void
    {
        $product = $this->product('black-abaya');

        $product->update(['price' => 60]);

        $this->assertSame(0, ProductSlugRedirect::count());
    }
}
