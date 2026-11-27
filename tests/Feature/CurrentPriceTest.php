<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrentPriceTest extends TestCase
{
    use RefreshDatabase;

    private function cartWithPriceChange(float $oldPrice, float $newPrice): array
    {
        config(['shop.shipping.free_threshold' => 1000, 'shop.shipping.flat_rate' => 4.95]);
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create(['price' => $oldPrice, 'stock' => 10, 'is_active' => true]);
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2, 'status' => 'active', 'price_at_time' => $oldPrice]);
        $product->update(['price' => $newPrice]); // price changes after it was added to the cart

        return [$user, $product];
    }

    public function test_checkout_charges_the_current_price_not_the_price_when_added(): void
    {
        [$user] = $this->cartWithPriceChange(20, 25);

        $this->actingAs($user)->post('/en/checkout', [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect();

        $order = Order::with('items')->sole();
        $this->assertEqualsWithDelta(50, (float) $order->subtotal, 0.001);
        $this->assertEqualsWithDelta(25, (float) $order->items->first()->price, 0.001);
    }

    public function test_cart_and_checkout_show_the_current_price_and_flag_the_change(): void
    {
        [$user] = $this->cartWithPriceChange(20, 25);

        $this->actingAs($user)->get('/en/cart')->assertOk()
            ->assertSee('€25.00')
            ->assertSee('Price changed since you added it (was €20.00).');

        $this->actingAs($user)->get('/en/checkout')->assertOk()->assertSee('€50.00');
    }

    public function test_no_note_when_the_price_did_not_change(): void
    {
        [$user] = $this->cartWithPriceChange(20, 20);

        $this->actingAs($user)->get('/en/cart')->assertOk()->assertDontSee('Price changed');
    }
}
