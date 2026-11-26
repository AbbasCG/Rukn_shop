<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAndSearchTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::factory()->for(Category::factory())->create(array_merge([
            'is_active' => true,
            'stock' => 5,
            'price' => 10,
        ], $attributes));
    }

    private function checkoutData(): array
    {
        return [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0612345678',
            'address_line1' => 'Teststraat 1',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
            'country' => 'NL',
            'shipping_method' => 'flat',
            'payment_method' => 'cod',
        ];
    }

    // ---- Search ----

    public function test_search_matches_short_and_long_description_case_insensitively(): void
    {
        $this->product(['name' => 'Blue Abaya', 'short_description' => 'Soft crepe fabric', 'long_description' => 'Made in Turkey']);
        $this->product(['name' => 'Black Kimono', 'short_description' => 'Linen', 'long_description' => 'Lightweight']);

        $this->get('/en/products?q=CREPE')
            ->assertOk()
            ->assertSee('Blue Abaya')
            ->assertDontSee('Black Kimono');

        $this->get('/en/products?q=turkey')->assertOk()->assertSee('Blue Abaya');
    }

    public function test_search_never_returns_inactive_products(): void
    {
        $this->product(['name' => 'Hidden Abaya', 'is_active' => false, 'short_description' => 'Secret item']);
        $this->product(['name' => 'Visible Abaya', 'short_description' => 'Public item']);

        $this->get('/en/products?q=item')
            ->assertOk()
            ->assertSee('Visible Abaya')
            ->assertDontSee('Hidden Abaya');
    }

    public function test_search_treats_wildcards_literally(): void
    {
        $this->product(['name' => 'Plain Abaya']);

        $this->get('/en/products?q=%25')->assertOk()->assertDontSee('Plain Abaya');
    }

    // ---- Add to cart ----

    public function test_out_of_stock_product_cannot_be_added_to_cart(): void
    {
        $product = $this->product(['stock' => 0]);

        $this->postJson('/en/cart', ['product_id' => $product->id, 'quantity' => 1])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->assertEmpty(session('cart.items', []));
    }

    public function test_inactive_product_cannot_be_added_to_cart(): void
    {
        $product = $this->product(['is_active' => false]);

        $this->postJson('/en/cart', ['product_id' => $product->id])->assertStatus(422);
    }

    public function test_guest_cart_quantity_is_capped_at_stock(): void
    {
        $product = $this->product(['stock' => 3]);

        $this->postJson('/en/cart', ['product_id' => $product->id, 'quantity' => 10])
            ->assertOk()
            ->assertJson(['ok' => true, 'count' => 3]);

        $this->assertSame(3, session('cart.items')[$product->id]);

        // Already at the limit: adding more is refused.
        $this->postJson('/en/cart', ['product_id' => $product->id, 'quantity' => 1])->assertStatus(422);
    }

    public function test_user_cart_quantity_is_capped_at_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 4]);

        $this->actingAs($user)->postJson('/en/cart', ['product_id' => $product->id, 'quantity' => 3])->assertOk();
        $this->actingAs($user)->postJson('/en/cart', ['product_id' => $product->id, 'quantity' => 3])
            ->assertOk()
            ->assertJson(['count' => 4]);

        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 4]);
    }

    public function test_cart_update_is_capped_at_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 2]);
        $cart = Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => 1, 'status' => 'active', 'price_at_time' => $product->price,
        ]);

        $this->actingAs($user)->patch("/en/cart/{$cart->id}", ['quantity' => 9])
            ->assertSessionHas('warning');

        $this->assertSame(2, (int) $cart->fresh()->quantity);
    }

    // ---- Checkout ----

    public function test_checkout_decrements_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 5]);
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => 2, 'status' => 'active', 'price_at_time' => $product->price,
        ]);

        $this->actingAs($user)->post('/en/checkout', $this->checkoutData())->assertRedirect();

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseMissing('carts', ['user_id' => $user->id]);
    }

    public function test_checkout_is_refused_when_stock_dropped_below_cart_quantity(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 5]);
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => 4, 'status' => 'active', 'price_at_time' => $product->price,
        ]);
        $product->update(['stock' => 1]); // someone else bought most of it

        $this->actingAs($user)->post('/en/checkout', $this->checkoutData())
            ->assertRedirect(route('cart.index', ['locale' => 'en']))
            ->assertSessionHas('error');

        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'quantity' => 4]);
    }

    public function test_checkout_is_refused_for_deactivated_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => 1, 'status' => 'active', 'price_at_time' => $product->price,
        ]);
        $product->update(['is_active' => false]);

        $this->actingAs($user)->post('/en/checkout', $this->checkoutData())->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    // ---- Guest cart merge on login ----

    public function test_guest_cart_merge_is_capped_at_stock(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $product = $this->product(['stock' => 3]);
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => 2, 'status' => 'active', 'price_at_time' => $product->price,
        ]);

        $this->withSession(['cart.items' => [$product->id => 5]])
            ->post(route('login', ['locale' => 'en']), ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 3]);
    }

    public function test_checkout_rejects_the_old_mock_payment_method(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => 1, 'status' => 'active', 'price_at_time' => $product->price,
        ]);

        $this->actingAs($user)
            ->post('/en/checkout', array_merge($this->checkoutData(), ['payment_method' => 'mock']))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
    }
}
