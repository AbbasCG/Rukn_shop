<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['shop.shipping.free_threshold' => 500, 'shop.shipping.flat_rate' => 4.95]);
        $this->category = Category::factory()->create();
    }

    private function product(array $attributes = []): Product
    {
        return Product::factory()->for($this->category)->create($attributes + ['name' => 'Gold Mabkhara', 'price' => 100, 'stock' => 10, 'is_active' => true]);
    }

    private function onSale(Product $product, string $type, float $value, ?Carbon $starts = null, ?Carbon $ends = null): Product
    {
        $product->forceFill(['sale_type' => $type, 'sale_value' => $value, 'sale_starts_at' => $starts, 'sale_ends_at' => $ends])->save();

        return $product->fresh();
    }

    private function checkout()
    {
        return $this->post('/en/checkout', [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ]);
    }

    // ---- Pricing ----

    public function test_percentage_and_new_price(): void
    {
        $percent = $this->onSale($this->product(['price' => 79.95]), 'percent', 20);
        $this->assertSame(63.96, $percent->currentPrice());
        $this->assertSame(['percent' => 20, 'upTo' => false], $percent->discount());

        $fixed = $this->onSale($this->product(['price' => 100]), 'price', 75);
        $this->assertSame(75.0, $fixed->currentPrice());
        $this->assertSame(25, $fixed->discount()['percent']);

        $this->assertSame(100.0, $this->product()->currentPrice());
        $this->assertSame(0, $this->product()->discount()['percent']);
    }

    public function test_dates(): void
    {
        $product = $this->onSale($this->product(), 'percent', 10, now()->addDay(), now()->addDays(5));
        $this->assertFalse($product->saleIsRunning());
        $this->assertTrue($product->saleIsScheduled());
        $this->assertSame(100.0, $product->currentPrice());

        $this->travel(2)->days();
        $this->assertTrue($product->saleIsRunning());
        $this->assertSame(90.0, $product->currentPrice());

        $this->travel(4)->days();
        $this->assertFalse($product->saleIsRunning());
        $this->assertSame(100.0, $product->currentPrice());
    }

    public function test_variants_percentage_applies_to_each_own_price(): void
    {
        $product = $this->product(['price' => 100]);
        $a = ProductVariant::create(['product_id' => $product->id, 'size' => '52', 'stock' => 3]);
        $b = ProductVariant::create(['product_id' => $product->id, 'size' => '58', 'price' => 120, 'stock' => 3]);
        $product = $this->onSale($product, 'percent', 25)->load('variants');

        $this->assertSame([75.0, 90.0], $product->priceRange());
        $this->assertSame([100.0, 120.0], $product->regularPriceRange());
        $this->assertSame(['percent' => 25, 'upTo' => false], $product->discount());
    }

    public function test_variants_new_price_uses_own_reduced_price_or_none(): void
    {
        $product = $this->product(['price' => 100]);
        $plain = ProductVariant::create(['product_id' => $product->id, 'size' => '52', 'stock' => 3]);
        $own = ProductVariant::create(['product_id' => $product->id, 'size' => '58', 'price' => 120, 'sale_price' => 90, 'stock' => 3]);
        $none = ProductVariant::create(['product_id' => $product->id, 'size' => '60', 'price' => 130, 'stock' => 3]);
        $product = $this->onSale($product, 'price', 80)->load('variants');

        $prices = $product->variants->mapWithKeys(fn ($v) => [$v->size => $v->setRelation('product', $product)->effectivePrice()]);
        $this->assertSame(['52' => 80.0, '58' => 90.0, '60' => 130.0], $prices->all());
        $this->assertSame(['percent' => 25, 'upTo' => true], $product->discount());   // 20% and 25%
    }

    // ---- Storefront ----

    public function test_card_and_product_page_show_both_prices_and_a_badge(): void
    {
        $product = $this->onSale($this->product(['price' => 100]), 'percent', 20, null, now()->addDays(3));

        $this->get('/en/products')->assertOk()
            ->assertSee('€80.00')->assertSee('<del', false)->assertSee('€100.00')->assertSee('-20%');

        $this->get('/en/products/' . $product->slug)->assertOk()
            ->assertSee('€80.00')->assertSee('Regular price')->assertSee('€100.00')
            ->assertSee('Offer valid until')
            ->assertSee('"price":"80.00"', false)
            ->assertSee('"priceValidUntil"', false);
    }

    public function test_badge_in_arabic(): void
    {
        $this->onSale($this->product(), 'percent', 15);

        $this->get('/ar/products')->assertSee('<bdi dir="ltr">-15%</bdi>', false);
    }

    public function test_no_badge_or_crossed_out_price_without_a_reduction(): void
    {
        $this->product();
        $this->onSale($this->product(['name' => 'Later']), 'percent', 30, now()->addWeek());

        $this->get('/en/products')->assertDontSee('<del', false)->assertDontSee('-30%');
    }

    public function test_filter_and_sort_use_the_reduced_price(): void
    {
        $this->onSale($this->product(['name' => 'Reduced', 'price' => 60]), 'price', 20);
        $this->product(['name' => 'Plain Abaya', 'price' => 40]);

        $this->get('/en/products?price_range=0-25')->assertSee('Reduced')->assertDontSee('Plain Abaya');
        $this->get('/en/products?max_price=30')->assertSee('Reduced')->assertDontSee('Plain Abaya');

        $names = $this->get('/en/products?sort=price_low')->viewData('products')->pluck('name')->all();
        $this->assertSame(['Reduced', 'Plain Abaya'], $names);
    }

    // ---- Cart & order ----

    public function test_cart_checkout_and_order_use_the_reduced_price(): void
    {
        $product = $this->onSale($this->product(['price' => 100]), 'percent', 20);

        $this->postJson('/en/cart', ['product_id' => $product->id, 'quantity' => 2])->assertOk();
        $this->get('/en/cart')->assertSee('€80.00')->assertSee('€160.00')->assertSee('Regular price');
        $this->get('/en/checkout')->assertSee('€160.00');

        $this->checkout()->assertRedirect();
        $order = Order::sole();
        $this->assertEquals(80.0, (float) $order->items->first()->price);
        $this->assertEquals(160.0, (float) $order->subtotal);
        $this->assertSame(8, (int) $product->fresh()->stock);
    }

    public function test_a_reduction_that_ends_before_checkout_is_not_applied(): void
    {
        $product = $this->onSale($this->product(['price' => 100]), 'percent', 20, null, now()->addHour());
        $this->postJson('/en/cart', ['product_id' => $product->id]);

        $this->travel(2)->hours();
        $this->checkout();

        $this->assertEquals(100.0, (float) Order::sole()->items->first()->price);
    }

    public function test_logged_in_cart_uses_the_reduced_price(): void
    {
        $product = $this->onSale($this->product(['price' => 50]), 'price', 35);
        $this->actingAs(User::factory()->create());

        $this->postJson('/en/cart', ['product_id' => $product->id])->assertOk();
        $this->assertEquals(35.0, (float) \App\Models\Cart::sole()->price_at_time);
        $this->get('/en/cart')->assertSee('€35.00');
    }

    // ---- Admin ----

    private function form(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Gold Mabkhara', 'category_id' => $this->category->id, 'price' => '100', 'stock' => '10',
            'short_description' => 'Nice', 'is_active' => '1',
        ];
    }

    public function test_admin_sets_a_reduction_with_dates_in_dutch_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();

        $this->actingAs($admin)->put("/en/admin/products/{$product->id}", $this->form([
            'sale_type' => 'percent', 'sale_value' => '20', 'sale_start' => '2026-12-01', 'sale_end' => '2026-12-31',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertSame('percent', $product->sale_type);
        // Whole Dutch days: 1 Dec 00:00 Amsterdam = 30 Nov 23:00 UTC; ends after 31 Dec.
        $this->assertSame('2026-11-30 23:00:00', $product->sale_starts_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-12-31 23:00:00', $product->sale_ends_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-12-31', $product->saleLastDay()->toDateString());

        $this->get("/en/admin/products/{$product->id}/edit")->assertOk()
            ->assertSee('Price reduction')->assertSee('value="2026-12-01"', false)->assertSee('value="2026-12-31"', false)
            ->assertSee('Starts on');

        // Removing it.
        $this->put("/en/admin/products/{$product->id}", $this->form(['sale_type' => '']));
        $this->assertNull($product->fresh()->sale_type);
        $this->assertNull($product->fresh()->sale_value);
    }

    public function test_admin_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();
        $this->actingAs($admin);

        $this->put("/en/admin/products/{$product->id}", $this->form(['sale_type' => 'percent', 'sale_value' => '100']))
            ->assertSessionHasErrors(['sale_value' => 'The percentage must be below 100.']);
        $this->put("/en/admin/products/{$product->id}", $this->form(['sale_type' => 'price', 'sale_value' => '120']))
            ->assertSessionHasErrors(['sale_value' => 'The reduced price must be lower than the regular price.']);
        $this->put("/en/admin/products/{$product->id}", $this->form(['sale_type' => 'price']))
            ->assertSessionHasErrors('sale_value');
        $this->put("/en/admin/products/{$product->id}", $this->form(['sale_type' => 'percent', 'sale_value' => '10', 'sale_start' => '2026-12-10', 'sale_end' => '2026-12-01']))
            ->assertSessionHasErrors('sale_end');
        $this->assertNull($product->fresh()->sale_type);
    }

    public function test_admin_variant_reduced_prices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();

        $this->actingAs($admin)->put("/en/admin/products/{$product->id}", $this->form([
            'sale_type' => 'price', 'sale_value' => '80',
            'size_system' => 'lengths', 'sizes' => ['52', '58'],
            'variants' => [
                ['size' => '52', 'color_key' => '', 'stock' => '3', 'price' => ''],
                ['size' => '58', 'color_key' => '', 'stock' => '3', 'price' => '120', 'sale_price' => '95'],
            ],
        ]))->assertSessionHasNoErrors();

        $variants = $product->fresh()->variants->keyBy('size');
        $this->assertNull($variants['52']->sale_price);
        $this->assertEquals(95.0, (float) $variants['58']->sale_price);

        $this->put("/en/admin/products/{$product->id}", $this->form([
            'sale_type' => 'price', 'sale_value' => '80', 'size_system' => 'lengths', 'sizes' => ['58'],
            'variants' => [['size' => '58', 'color_key' => '', 'stock' => '3', 'price' => '120', 'sale_price' => '130']],
        ]))->assertSessionHasErrors('variants.0.sale_price');
    }

    public function test_thirty_day_rule_warnings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product(['price' => 100]);
        $this->actingAs($admin);

        // Price raised, then a reduction right away: warned.
        $this->put("/en/admin/products/{$product->id}", $this->form(['price' => '120']));
        $this->assertNotNull($product->fresh()->regular_price_raised_at);
        $this->put("/en/admin/products/{$product->id}", $this->form(['price' => '120', 'sale_type' => 'percent', 'sale_value' => '20']))
            ->assertSessionHas('warning', 'Check the price reduction: see the warning on the product (30-day rule).');
        $this->get("/en/admin/products/{$product->id}/edit")->assertSee('The regular price was raised on');

        // A price decrease is never flagged.
        $other = $this->product(['name' => 'Other', 'price' => 100]);
        $this->put("/en/admin/products/{$other->id}", $this->form(['name' => 'Other', 'price' => '90']));
        $this->assertNull($other->fresh()->regular_price_raised_at);
    }

    public function test_a_new_reduction_soon_after_an_earlier_one_is_flagged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->onSale($this->product(), 'percent', 10);
        $this->actingAs($admin);

        $this->travel(3)->days();
        $this->put("/en/admin/products/{$product->id}", $this->form(['sale_type' => '']));   // ends now
        $this->assertNotNull($product->fresh()->last_sale_ended_at);

        $this->travel(5)->days();
        $this->put("/en/admin/products/{$product->id}", $this->form(['sale_type' => 'percent', 'sale_value' => '30']));
        $this->assertSame('recent_sale', $product->fresh()->saleReferenceWarnings()[0]['key']);

        $this->travel(40)->days();
        $this->put("/en/admin/products/{$product->id}", $this->form(['sale_type' => 'percent', 'sale_value' => '25']));
        // Changing a running reduction: the previous (lower) price was in force
        // until now, so the new one is flagged too.
        $fresh = $product->fresh();
        $this->assertSame(['recent_sale'], array_column($fresh->saleReferenceWarnings(), 'key'));
    }

    public function test_admin_list_marks_reduced_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->onSale($this->product(['price' => 100]), 'percent', 20);

        $this->actingAs($admin)->get('/en/admin/products')->assertSee('€80.00')->assertSee('€100.00')->assertSee('Sale');
    }
}
