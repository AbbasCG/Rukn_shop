<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Shipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'shop.shipping.flat_rate' => 4.95,
            'shop.shipping.free_threshold' => 50,
            'shop.shipping.delivery_days_min' => 1,
            'shop.shipping.delivery_days_max' => 3,
            'shop.returns.days' => 14,
        ]);
    }

    private function checkoutWith(float $price, int $quantity): Order
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'price' => $price, 'stock' => 10, 'is_active' => true,
        ]);
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => $quantity, 'status' => 'active', 'price_at_time' => $price,
        ]);

        $this->actingAs($user)->post('/en/checkout', [
            'name' => 'Test', 'email' => 'test@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect();

        return Order::firstOrFail();
    }

    public function test_shipping_cost_follows_the_free_threshold(): void
    {
        $this->assertSame(0.0, Shipping::cost(0));
        $this->assertSame(4.95, Shipping::cost(49.99));
        $this->assertSame(0.0, Shipping::cost(50));
        $this->assertSame(0.0, Shipping::cost(120));
        $this->assertSame(10.01, Shipping::remainingForFree(39.99));
        $this->assertSame(0.0, Shipping::remainingForFree(75));
    }

    public function test_order_below_threshold_pays_the_flat_rate(): void
    {
        $order = $this->checkoutWith(20, 2); // 40.00

        $this->assertEqualsWithDelta(4.95, (float) $order->shipping_cost, 0.001);
        $this->assertEqualsWithDelta(44.95, (float) $order->total, 0.001);
    }

    public function test_order_at_threshold_ships_free(): void
    {
        $order = $this->checkoutWith(25, 2); // 50.00

        $this->assertEqualsWithDelta(0, (float) $order->shipping_cost, 0.001);
        $this->assertEqualsWithDelta(50, (float) $order->total, 0.001);
    }

    public function test_cart_shows_shipping_and_the_free_shipping_hint(): void
    {
        $product = Product::factory()->for(Category::factory())->create(['price' => 30, 'stock' => 5, 'is_active' => true]);

        $this->withSession(['cart.items' => [$product->id => 1]])
            ->get('/en/cart')
            ->assertOk()
            ->assertSee('€4.95')
            ->assertSee('Add €20.00 more for free shipping.');
    }

    public function test_policy_texts_use_the_configured_values_in_every_language(): void
    {
        foreach (['en', 'nl', 'ar'] as $locale) {
            $faq = $this->get("/{$locale}/faq")->assertOk();
            $faq->assertDontSee(':threshold')->assertDontSee(':return_days')->assertDontSee(':min_days');
            $faq->assertSee('id="faq-shipping"', false)->assertSee('id="faq-returns"', false);

            $this->get("/{$locale}/terms-of-service")->assertOk()->assertSee('14')->assertDontSee(':return_days');
        }

        $this->get('/en/faq')->assertSee('1–3 business days')->assertSee('€50.00')->assertSee('€4.95');
        $this->get('/nl/faq')->assertSee("€\u{00A0}50,00")->assertSee('14 dagen');
        $this->get('/ar/faq')->assertSee('14 يوماً');
    }

    public function test_no_page_still_promises_card_payments_or_tracking_emails(): void
    {
        foreach (['/en/faq', '/en/contact', '/en/about'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('PayPal')
                ->assertDontSee('Apple Pay')
                ->assertDontSee('tracking link')
                ->assertDontSee('Express options');
        }
    }
}
