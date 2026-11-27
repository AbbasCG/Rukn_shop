<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiddenProductAndErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    private function hiddenProduct(): Product
    {
        return Product::factory()->for(Category::factory())->create([
            'name' => 'Draft Abaya', 'slug' => 'draft-abaya', 'is_active' => false,
        ]);
    }

    public function test_hidden_products_are_not_found_for_visitors_and_customers(): void
    {
        $product = $this->hiddenProduct();

        $this->get('/en/products/draft-abaya')->assertNotFound();
        $this->get("/en/products/{$product->id}")->assertNotFound(); // old link: 404, not a redirect to a 404

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get('/en/products/draft-abaya')->assertNotFound();
    }

    public function test_admins_can_preview_hidden_products_with_a_notice(): void
    {
        $this->hiddenProduct();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/en/products/draft-abaya')
            ->assertOk()
            ->assertSee('This product is hidden')
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_404_page_is_translated_and_right_to_left_in_arabic(): void
    {
        $this->get('/ar/no-such-page')->assertNotFound()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('الصفحة غير موجودة')
            ->assertSee('href="' . url('/ar/products') . '"', false);
    }

    public function test_404_for_unknown_urls_without_a_language_uses_dutch(): void
    {
        // No route matches, so no session or locale middleware ran.
        $this->get('/totally/unknown')->assertNotFound()
            ->assertSee('<html lang="nl" dir="ltr">', false)
            ->assertSee('Pagina niet gevonden');
    }

    public function test_403_page_is_shown_for_forbidden_areas(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get('/en/admin')->assertForbidden()->assertSee('No access');
    }

    public function test_429_page_explains_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/nl/contact', ['name' => '']);
        }

        $this->post('/nl/contact', ['name' => ''])->assertStatus(429)->assertSee('Te veel verzoeken');
    }
}
