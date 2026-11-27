<?php

namespace Tests\Feature;

use App\Mail\NewOrderNotification;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductVariantsTest extends TestCase
{
    use RefreshDatabase;

    private Product $abaya;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['shop.shipping.free_threshold' => 1000, 'shop.shipping.flat_rate' => 0]);

        // Abaya in lengths 52/54 and black/navy; 54 black costs more.
        $this->abaya = Product::factory()->for(Category::factory())->create([
            'name' => 'Black Abaya', 'slug' => 'black-abaya', 'price' => 49, 'stock' => 0, 'is_active' => true,
            'size_system' => 'lengths', 'sizes' => ['52', '54'],
            'colors' => [
                ['key' => 'black', 'hex' => '#111111', 'names' => ['nl' => 'Zwart', 'en' => 'Black', 'ar' => 'أسود']],
                ['key' => 'navy', 'hex' => '#1f2a44', 'names' => ['nl' => 'Marineblauw', 'en' => 'Navy', 'ar' => 'كحلي']],
            ],
        ]);
        foreach ([['52', 'black', 3, null], ['52', 'navy', 0, null], ['54', 'black', 5, 59], ['54', 'navy', 1, null]] as [$size, $color, $stock, $price]) {
            $this->abaya->variants()->create(['size' => $size, 'color_key' => $color, 'stock' => $stock, 'price' => $price]);
        }
        $this->abaya->syncStockFromVariants();
    }

    private function variant(string $size, string $color): ProductVariant
    {
        return $this->abaya->variants()->where(['size' => $size, 'color_key' => $color])->sole();
    }

    private function checkout(User $user, string $locale = 'en')
    {
        return $this->actingAs($user)->post("/{$locale}/checkout", [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ]);
    }

    // ---- Model ----

    public function test_variant_labels_prices_and_stock_sum(): void
    {
        $this->assertSame(9, $this->abaya->fresh()->stock);
        $this->assertSame('54 · أسود', $this->variant('54', 'black')->label('ar'));
        $this->assertSame('52 · Marineblauw', $this->variant('52', 'navy')->label('nl'));
        $this->assertSame(59.0, $this->variant('54', 'black')->effectivePrice());
        $this->assertSame(49.0, $this->variant('52', 'black')->effectivePrice());
        $this->assertSame([49.0, 59.0], $this->abaya->fresh()->priceRange());
    }

    // ---- Admin ----

    public function test_admin_saves_sizes_colours_and_per_variant_stock_and_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/en/admin/products', [
            'name' => 'Navy Abaya', 'category_id' => $this->abaya->category_id, 'price' => 40, 'stock' => 999,
            'short_description' => 'Soft.', 'is_active' => 1,
            'size_system' => 'letters', 'sizes' => ['L', 'S', 'NOT-A-SIZE'],
            'colors' => [['key' => 'navy', 'hex' => '#1f2a44', 'names' => ['en' => 'Navy', 'ar' => 'كحلي']]],
            'variants' => [
                ['size' => 'S', 'color_key' => 'navy', 'stock' => 2, 'price' => ''],
                ['size' => 'L', 'color_key' => 'navy', 'stock' => 4, 'price' => '45'],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $product = Product::where('name', 'Navy Abaya')->with('variants')->sole();
        $this->assertSame(['S', 'L'], $product->sizes);              // list order, unknown size dropped
        $this->assertSame(6, $product->stock);                        // sum of variants, not the 999 typed
        $this->assertCount(2, $product->variants);
        $this->assertNull($product->variants->firstWhere('size', 'S')->price);
        $this->assertEquals(45, $product->variants->firstWhere('size', 'L')->price);
    }

    public function test_a_colour_needs_a_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/en/admin/products', [
            'name' => 'X', 'category_id' => $this->abaya->category_id, 'price' => 40, 'stock' => 1,
            'short_description' => 'Soft.', 'colors' => [['key' => 'c1', 'hex' => '#000000', 'names' => ['en' => ' ']]],
        ])->assertSessionHasErrors('colors.0.names');
    }

    public function test_removing_a_colour_removes_its_variants_but_orders_keep_the_label(): void
    {
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id, 'product_id' => $this->abaya->id, 'variant_id' => $this->variant('54', 'navy')->id, 'quantity' => 1, 'status' => 'active']);
        $this->checkout($user)->assertRedirect();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch('/en/admin/products/black-abaya', [
            'name' => 'Black Abaya', 'category_id' => $this->abaya->category_id, 'price' => 49, 'stock' => 0,
            'short_description' => 'Soft.', 'is_active' => 1,
            'size_system' => 'lengths', 'sizes' => ['52', '54'],
            'colors' => [['key' => 'black', 'hex' => '#111111', 'names' => ['en' => 'Black']]],
            'variants' => [
                ['size' => '52', 'color_key' => 'black', 'stock' => 3],
                ['size' => '54', 'color_key' => 'black', 'stock' => 5, 'price' => 59],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(2, $this->abaya->variants()->count());
        $this->assertSame(8, $this->abaya->fresh()->stock);
        $item = Order::sole()->items()->sole();
        $this->assertNull($item->variant_id);
        $this->assertSame('54 · Navy', $item->variant_label);
    }

    public function test_clearing_sizes_and_colours_turns_variants_off(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch('/en/admin/products/black-abaya', [
            'name' => 'Black Abaya', 'category_id' => $this->abaya->category_id, 'price' => 49, 'stock' => 7,
            'short_description' => 'Soft.', 'is_active' => 1, 'size_system' => '',
        ])->assertSessionHasNoErrors();

        $product = $this->abaya->fresh();
        $this->assertSame(0, $product->variants()->count());
        $this->assertNull($product->sizes);
        $this->assertSame(7, $product->stock);
    }

    // ---- Storefront ----

    public function test_product_page_offers_the_picker_and_a_price_range(): void
    {
        $response = $this->get('/ar/products/black-abaya')->assertOk()
            ->assertSee('x-data="productOptions(', false)
            ->assertSee('اختر المقاس واللون')
            ->assertSee('طول العباية بالإنش');

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $m);
        $offers = json_decode($m[1], true)[0]['offers'];
        $this->assertSame('AggregateOffer', $offers['@type']);
        $this->assertSame('49.00', $offers['lowPrice']);
        $this->assertSame('59.00', $offers['highPrice']);
    }

    public function test_shop_card_links_to_the_product_page_with_a_from_price(): void
    {
        $this->get('/en/products')->assertOk()
            ->assertSee('Choose size and colour')
            ->assertSee('From €49.00');
    }

    public function test_products_without_variants_work_as_before(): void
    {
        $plain = Product::factory()->for($this->abaya->category)->create(['slug' => 'mabkhara', 'stock' => 4, 'is_active' => true]);

        $this->get('/en/products/mabkhara')->assertOk()->assertDontSee('productOptions(', false);
        $this->postJson('/en/cart', ['product_id' => $plain->id])->assertOk()->assertJson(['ok' => true]);
    }

    // ---- Cart ----

    public function test_a_variant_must_be_chosen_and_belong_to_the_product(): void
    {
        $other = Product::factory()->for($this->abaya->category)->create(['is_active' => true]);
        $foreign = $other->variants()->create(['size' => 'M', 'stock' => 5]);

        $this->postJson('/en/cart', ['product_id' => $this->abaya->id])
            ->assertStatus(422)->assertJson(['message' => 'Please choose a size and colour first.']);
        $this->postJson('/en/cart', ['product_id' => $this->abaya->id, 'variant_id' => $foreign->id])->assertStatus(422);
    }

    public function test_sold_out_variant_cannot_be_added_and_quantity_is_capped_per_variant(): void
    {
        $this->postJson('/en/cart', ['product_id' => $this->abaya->id, 'variant_id' => $this->variant('52', 'navy')->id])->assertStatus(422);

        $this->postJson('/en/cart', ['product_id' => $this->abaya->id, 'variant_id' => $this->variant('52', 'black')->id, 'quantity' => 10])
            ->assertOk()->assertJson(['count' => 3]);
    }

    public function test_logged_in_customer_can_add_two_sizes_of_the_same_product(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/en/cart', ['product_id' => $this->abaya->id, 'variant_id' => $this->variant('52', 'black')->id])->assertOk();
        $this->actingAs($user)->postJson('/en/cart', ['product_id' => $this->abaya->id, 'variant_id' => $this->variant('54', 'black')->id])
            ->assertOk()->assertJson(['count' => 2]);

        $this->assertSame(2, Cart::where('user_id', $user->id)->count());
    }

    public function test_cart_shows_variant_label_and_variant_price(): void
    {
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id, 'product_id' => $this->abaya->id, 'variant_id' => $this->variant('54', 'black')->id, 'quantity' => 2, 'status' => 'active', 'price_at_time' => 59]);

        $this->actingAs($user)->get('/en/cart')->assertOk()->assertSee('54 · Black')->assertSee('€118.00');
    }

    public function test_guest_cart_keeps_variants_apart_and_merges_on_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $black52 = $this->variant('52', 'black');
        $black54 = $this->variant('54', 'black');

        $this->postJson('/en/cart', ['product_id' => $this->abaya->id, 'variant_id' => $black52->id, 'quantity' => 2])->assertOk();
        $this->postJson('/en/cart', ['product_id' => $this->abaya->id, 'variant_id' => $black54->id])->assertOk();
        $this->assertSame(["{$this->abaya->id}:{$black52->id}" => 2, "{$this->abaya->id}:{$black54->id}" => 1], session('cart.items'));

        $this->get('/en/cart')->assertSee('52 · Black')->assertSee('54 · Black');

        $this->post('/en/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'variant_id' => $black52->id, 'quantity' => 2]);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'variant_id' => $black54->id, 'quantity' => 1]);
    }

    // ---- Checkout & orders ----

    public function test_checkout_uses_variant_price_takes_variant_stock_and_records_the_label(): void
    {
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id, 'product_id' => $this->abaya->id, 'variant_id' => $this->variant('54', 'black')->id, 'quantity' => 2, 'status' => 'active']);

        $this->checkout($user, 'ar')->assertRedirect();

        $item = Order::sole()->items()->sole();
        $this->assertEquals(59, $item->price);
        $this->assertSame('54 · أسود', $item->variant_label);
        $this->assertSame(3, $this->variant('54', 'black')->stock);
        $this->assertSame(7, $this->abaya->fresh()->stock);
        $this->assertEqualsWithDelta(118, (float) Order::sole()->total, 0.001);
    }

    public function test_checkout_is_refused_when_the_variant_ran_out(): void
    {
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id, 'product_id' => $this->abaya->id, 'variant_id' => $this->variant('54', 'navy')->id, 'quantity' => 1, 'status' => 'active']);
        $this->variant('54', 'navy')->update(['stock' => 0]);

        $this->checkout($user)->assertRedirect(route('cart.index', ['locale' => 'en']))->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cancelling_an_order_returns_stock_to_the_variant(): void
    {
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id, 'product_id' => $this->abaya->id, 'variant_id' => $this->variant('52', 'black')->id, 'quantity' => 2, 'status' => 'active']);
        $this->checkout($user);
        $this->assertSame(1, $this->variant('52', 'black')->stock);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch('/en/admin/orders/' . Order::sole()->id, ['status' => 'cancelled'])->assertSessionMissing('error');

        $this->assertSame(3, $this->variant('52', 'black')->stock);
        $this->assertSame(9, $this->abaya->fresh()->stock);
    }

    public function test_order_page_and_staff_email_show_the_variant(): void
    {
        config(['shop.order_notifications.locale' => 'en', 'shop.order_notifications.low_stock_threshold' => 2]);
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id, 'product_id' => $this->abaya->id, 'variant_id' => $this->variant('52', 'black')->id, 'quantity' => 2, 'status' => 'active']);
        $this->checkout($user);
        $order = Order::sole();

        $this->actingAs($user)->get("/en/orders/{$order->id}")->assertOk()->assertSee('52 · Black');

        $mail = new NewOrderNotification($order);
        $mail->assertSeeInHtml('52 · Black');
        $mail->assertSeeInHtml('Black Abaya (52 · Black) – 1 left'); // low stock per variant, not the product total
    }
}
