<?php

namespace Tests\Feature;

use App\Mail\BackInStock;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockNotification;
use App\Models\User;
use App\Support\OrderStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BackInStockTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->product = Product::factory()->for(Category::factory())->create(['name' => 'Gold Mabkhara', 'price' => 40, 'stock' => 0, 'is_active' => true]);
    }

    private function ask(string $email = 'Fatima@Example.com', ?int $variantId = null, string $locale = 'en')
    {
        return $this->postJson("/{$locale}/products/{$this->product->slug}/notify", ['email' => $email, 'variant_id' => $variantId]);
    }

    private function abaya(): array
    {
        $abaya = Product::factory()->for(Category::factory())->create(['name' => 'Black Abaya', 'price' => 80, 'stock' => 2, 'is_active' => true, 'size_system' => 'lengths', 'sizes' => ['52', '54']]);
        $sold = ProductVariant::create(['product_id' => $abaya->id, 'size' => '52', 'stock' => 0]);
        $inStock = ProductVariant::create(['product_id' => $abaya->id, 'size' => '54', 'stock' => 2]);

        return [$abaya, $sold, $inStock];
    }

    // ---- Asking ----

    public function test_a_customer_asks_for_a_sold_out_product(): void
    {
        $this->ask()->assertOk()->assertJson(['ok' => true, 'message' => 'Done! We will email fatima@example.com as soon as it is available again.']);

        $request = StockNotification::sole();
        $this->assertSame('fatima@example.com', $request->email);
        $this->assertSame('en', $request->locale);
        $this->assertNull($request->variant_id);

        // Asking twice does not make two requests.
        $this->ask('fatima@example.com')->assertJson(['message' => 'You are already on the list for this. We will email you as soon as it is available again.']);
        $this->assertSame(1, StockNotification::count());
    }

    public function test_the_form_is_shown_only_when_sold_out(): void
    {
        $this->get("/en/products/{$this->product->slug}")->assertSee('Email me when it is available')->assertSee('notify', false);

        $this->product->update(['stock' => 3]);
        Mail::fake();
        $this->get("/en/products/{$this->product->slug}")->assertDontSee('Email me when it is available');
    }

    public function test_logged_in_customers_get_their_email_filled_in(): void
    {
        $user = User::factory()->create(['email' => 'mariam@example.com']);

        $this->actingAs($user)->get("/en/products/{$this->product->slug}")->assertSee('mariam@example.com');
        $this->actingAs($user)->ask('mariam@example.com')->assertOk();
        $this->assertSame($user->id, StockNotification::sole()->user_id);
    }

    public function test_refused_when_in_stock_or_invalid(): void
    {
        $this->ask('not-an-email')->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->product->update(['stock' => 2]);
        $this->ask()->assertUnprocessable()->assertJson(['message' => 'Good news: this is available right now.']);

        $this->product->update(['is_active' => false]);
        $this->ask()->assertNotFound();
        $this->assertSame(0, StockNotification::count());
    }

    public function test_sizes_need_a_sold_out_variant_of_this_product(): void
    {
        [$abaya, $sold, $inStock] = $this->abaya();
        $other = ProductVariant::create(['product_id' => $this->product->id, 'size' => '60', 'stock' => 0]);
        $url = "/en/products/{$abaya->slug}/notify";

        $this->postJson($url, ['email' => 'a@example.com'])->assertUnprocessable()->assertJson(['message' => 'Choose a size/colour first.']);
        $this->postJson($url, ['email' => 'a@example.com', 'variant_id' => $other->id])->assertUnprocessable();
        $this->postJson($url, ['email' => 'a@example.com', 'variant_id' => $inStock->id])->assertUnprocessable();
        $this->postJson($url, ['email' => 'a@example.com', 'variant_id' => $sold->id])->assertOk();

        $this->assertSame($sold->id, StockNotification::sole()->variant_id);
    }

    public function test_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->ask("p{$i}@example.com")->assertOk();
        }
        $this->ask('p9@example.com')->assertStatus(429);
    }

    // ---- Sending ----

    public function test_restocking_sends_one_email_in_the_customers_language(): void
    {
        $this->ask('a@example.com', locale: 'ar');
        $this->ask('b@example.com', locale: 'nl');

        $this->product->update(['stock' => 5]);

        Mail::assertQueued(BackInStock::class, 2);
        Mail::assertQueued(BackInStock::class, fn (BackInStock $mail) => $mail->hasTo('a@example.com') && $mail->locale === 'ar');
        Mail::assertQueued(BackInStock::class, function (BackInStock $mail) {
            if (! $mail->hasTo('b@example.com')) {
                return false;
            }
            $mail->assertSeeInHtml('Gold Mabkhara')->assertSeeInHtml('Bekijken en bestellen')
                ->assertSeeInHtml(route('products.show', ['locale' => 'nl', 'product' => $this->product]))
                ->assertSeeInText('Je ontvangt deze e-mail één keer');

            return true;
        });
        $this->assertSame(0, StockNotification::pending()->count());

        // Selling out and restocking again does not email them a second time.
        $this->product->update(['stock' => 0]);
        $this->product->update(['stock' => 3]);
        Mail::assertQueued(BackInStock::class, 2);
    }

    public function test_only_the_size_that_came_back_is_notified(): void
    {
        [$abaya, $sold] = $this->abaya();
        $sold54 = $abaya->variants()->where('size', '54')->first();
        $sold54->update(['stock' => 0]);
        Mail::fake();

        $this->postJson("/en/products/{$abaya->slug}/notify", ['email' => 'size52@example.com', 'variant_id' => $sold->id])->assertOk();
        $this->postJson("/en/products/{$abaya->slug}/notify", ['email' => 'size54@example.com', 'variant_id' => $sold54->id])->assertOk();

        $sold->update(['stock' => 4]);

        Mail::assertQueued(BackInStock::class, 1);
        Mail::assertQueued(BackInStock::class, function (BackInStock $mail) {
            $mail->assertSeeInHtml('52');

            return $mail->hasTo('size52@example.com');
        });
        $this->assertNull(StockNotification::where('email', 'size54@example.com')->value('notified_at'));
    }

    public function test_admin_restock_sends_the_emails(): void
    {
        $this->ask('a@example.com');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/en/admin/products')->assertSee('1 customer waiting for it');
        $this->actingAs($admin)->get("/en/admin/products/{$this->product->id}/edit")->assertSee('1 customer waiting for it');

        $this->actingAs($admin)->put("/en/admin/products/{$this->product->id}", [
            'name' => 'Gold Mabkhara', 'category_id' => $this->product->category_id, 'price' => '40', 'stock' => '6',
            'short_description' => 'Nice', 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        Mail::assertQueued(BackInStock::class, fn (BackInStock $mail) => $mail->hasTo('a@example.com'));
        $this->actingAs($admin)->get('/en/admin/products')->assertDontSee('customer waiting');
    }

    public function test_a_cancelled_order_putting_stock_back_sends_the_emails(): void
    {
        $this->ask('a@example.com');
        $order = Order::create([
            'status' => 'pending', 'payment_status' => 'open', 'payment_method' => 'cod', 'locale' => 'nl',
            'name' => 'X', 'email' => 'x@example.com', 'phone' => '1', 'address_line1' => 'S 1', 'postal_code' => '1234AB',
            'city' => 'A', 'country' => 'NL', 'subtotal' => 40, 'shipping_cost' => 0, 'total' => 40,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'product_name' => 'Gold Mabkhara', 'quantity' => 1, 'price' => 40]);

        OrderStock::release($order);

        Mail::assertQueued(BackInStock::class, 1);
    }

    public function test_hidden_products_wait_until_they_are_shown_again(): void
    {
        $this->ask('a@example.com');
        $this->product->update(['is_active' => false]);
        $this->product->update(['stock' => 4]);
        Mail::assertNothingQueued();

        $this->product->update(['is_active' => true]);
        Mail::assertQueued(BackInStock::class, 1);
    }

    // ---- Privacy ----

    public function test_old_requests_are_deleted(): void
    {
        $this->ask('old@example.com');
        $this->ask('sent@example.com');
        StockNotification::where('email', 'sent@example.com')->update(['notified_at' => now()]);

        $this->travel(31)->days();
        $this->artisan('model:prune', ['--model' => [StockNotification::class]]);
        $this->assertSame(['old@example.com'], StockNotification::pluck('email')->all());

        $this->travel(150)->days();
        $this->artisan('model:prune', ['--model' => [StockNotification::class]]);
        $this->assertSame(0, StockNotification::count());
    }

    public function test_privacy_policy_explains_it(): void
    {
        $this->get('/en/privacy-policy')->assertSee('When you ask to be told a product is back')->assertSee('after 180 days');
        $this->get('/nl/privacy-policy')->assertSee('Als je vraagt om bericht wanneer een product terug is');
        $this->get('/ar/privacy-policy')->assertSee('عندما تطلب إعلامك بعودة منتج');
    }

    public function test_translated(): void
    {
        $this->get("/ar/products/{$this->product->slug}")->assertSee('أعلمني عند التوفر');
        $this->get("/nl/products/{$this->product->slug}")->assertSee('Mail me als het weer leverbaar is');
    }
}
