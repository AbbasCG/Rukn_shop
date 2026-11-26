<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\order_items as OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_footer_has_no_dead_links(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();
        $footer = substr($html, strpos($html, '<footer'));

        $this->assertStringNotContainsString('href="#"', $footer);
        $this->assertStringContainsString('href="' . route('products.index', ['locale' => 'en', 'sort' => 'newest']) . '"', $footer);
        $this->assertStringContainsString('href="' . route('products.index', ['locale' => 'en', 'sort' => 'popular']) . '"', $footer);
        $this->assertStringNotContainsString('>Sale<', $footer);
    }

    public function test_only_configured_social_accounts_are_shown(): void
    {
        config(['shop.social' => ['instagram' => 'https://www.instagram.com/rukn_30/', 'facebook' => null, 'x' => '', 'tiktok' => null]]);

        $this->get('/en')
            ->assertSee('href="https://www.instagram.com/rukn_30/" target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('aria-label="Facebook"', false)
            ->assertDontSee('aria-label="X"', false)
            ->assertSee('Follow Us');

        config(['shop.social' => ['instagram' => null, 'facebook' => 'https://www.facebook.com/rukn', 'x' => null, 'tiktok' => null]]);
        $this->get('/en')->assertSee('aria-label="Facebook"', false)->assertDontSee('aria-label="Instagram"', false);
    }

    public function test_without_any_social_account_the_follow_section_is_hidden(): void
    {
        config(['shop.social' => []]);

        $html = $this->get('/en')->assertOk()->getContent();
        $this->assertStringNotContainsString('Follow Us', substr($html, strpos($html, '<footer')));
    }

    public function test_register_page_links_to_the_terms_and_privacy_policy(): void
    {
        $this->get('/en/register')->assertOk()
            ->assertSee('href="' . route('terms-of-service', ['locale' => 'en']) . '"', false)
            ->assertSee('href="' . route('privacy-policy', ['locale' => 'en']) . '"', false)
            ->assertDontSee('href="#"', false);
    }

    public function test_bestsellers_sort_orders_by_units_sold_ignoring_cancelled_orders(): void
    {
        $category = Category::factory()->create();
        $make = fn (string $name) => Product::factory()->for($category)->create(['name' => $name, 'stock' => 50, 'is_active' => true]);
        $few = $make('Few Sold');
        $many = $make('Many Sold');
        $cancelled = $make('Cancelled Only');
        $make('Never Sold');

        $this->sell($few, 2);
        $this->sell($many, 3);
        $this->sell($many, 4);
        $this->sell($cancelled, 20, 'cancelled');

        $names = $this->get('/en/products?sort=popular')->assertOk()
            ->viewData('products')->pluck('name')->all();

        $this->assertSame(['Many Sold', 'Few Sold'], array_slice($names, 0, 2));
        $this->assertCount(4, $names); // unsold products are still listed, after the sellers
    }

    public function test_the_sort_option_is_called_bestsellers(): void
    {
        $this->get('/en/products')->assertSee('Bestsellers');
        $this->get('/ar/products')->assertSee('الأكثر مبيعًا');
    }

    private function sell(Product $product, int $quantity, string $status = 'pending'): void
    {
        $order = Order::create([
            'status' => $status, 'payment_status' => 'open', 'payment_method' => 'cod',
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam', 'country' => 'NL',
            'subtotal' => 10, 'shipping_cost' => 0, 'total' => 10,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => $quantity, 'price' => 10]);
    }
}
