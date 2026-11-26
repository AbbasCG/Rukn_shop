<?php

namespace Tests\Feature;

use App\Mail\NewOrderNotification;
use App\Mail\OrderConfirmation;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['shop.shipping.free_threshold' => 1000, 'shop.shipping.flat_rate' => 0]);

        $this->product = Product::factory()->for(Category::factory())->create([
            'name' => 'Black Abaya', 'price' => 49, 'stock' => 5, 'is_active' => true,
        ]);
    }

    private function checkoutData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Fatima', 'email' => 'Fatima@Example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ], $overrides);
    }

    private function guestOrder(): Order
    {
        $this->postJson('/ar/cart', ['product_id' => $this->product->id, 'quantity' => 2])->assertOk();
        $this->post('/ar/checkout', $this->checkoutData())->assertRedirect();

        return Order::sole();
    }

    // ---- Placing the order ----

    public function test_cart_and_checkout_are_open_to_guests(): void
    {
        $this->postJson('/en/cart', ['product_id' => $this->product->id])->assertOk();

        $this->get('/en/cart')->assertOk()->assertSee('Checkout as guest');
        $this->get('/en/checkout')->assertOk()->assertSee('Have an account? Log in')->assertSee('Black Abaya');
    }

    public function test_a_guest_can_place_an_order(): void
    {
        $this->postJson('/ar/cart', ['product_id' => $this->product->id, 'quantity' => 2]);

        $response = $this->post('/ar/checkout', $this->checkoutData());

        $order = Order::sole();
        $this->assertNull($order->user_id);
        $this->assertTrue($order->isGuest());
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $order->access_token);
        $this->assertSame('ar', $order->locale);
        $this->assertSame(3, $this->product->fresh()->stock);
        $this->assertEmpty(session('cart.items'));   // guest cart emptied

        $response->assertRedirect(route('orders.placed', ['locale' => 'ar', 'order' => $order->id, 'token' => $order->access_token]));
    }

    public function test_confirmation_page_tells_the_guest_how_to_find_the_order_again(): void
    {
        $order = $this->guestOrder();

        $this->get("/ar/orders/placed/{$order->id}/{$order->access_token}")->assertOk()
            ->assertSee('Fatima@Example.com')
            ->assertSee('ابحث عن طلبي')
            ->assertSee($order->privateUrl('ar'), false);
    }

    public function test_guest_gets_the_confirmation_email_with_the_private_link(): void
    {
        $order = $this->guestOrder();

        Mail::assertSent(OrderConfirmation::class, fn ($mail) => $mail->hasTo('Fatima@Example.com'));
        Mail::assertSent(NewOrderNotification::class);
        (new OrderConfirmation($order))->assertSeeInHtml($order->privateUrl('ar'), false);
    }

    // ---- Private link ----

    public function test_the_private_link_shows_the_order_without_an_account(): void
    {
        $order = $this->guestOrder();

        $this->get($order->privateUrl('en'))->assertOk()
            ->assertSee('Black Abaya')
            ->assertDontSee('href="' . route('orders.index', ['locale' => 'en']) . '"', false); // no "my orders" for guests
    }

    public function test_a_wrong_or_missing_token_reveals_nothing(): void
    {
        $order = $this->guestOrder();
        $wrong = str_repeat('a', 40);

        $this->get("/en/orders/view/{$order->id}/{$wrong}")->assertNotFound();
        $this->get("/en/orders/placed/{$order->id}/{$wrong}")->assertNotFound();
        $this->get("/en/orders/view/{$order->id}/short")->assertNotFound();
        $this->get('/en/orders/view/99999/' . $order->access_token)->assertNotFound();
    }

    public function test_guest_orders_are_not_reachable_through_account_pages(): void
    {
        $order = $this->guestOrder();
        $someone = User::factory()->create();

        $this->actingAs($someone)->get("/en/orders/confirmation/{$order->id}")->assertNotFound();
        $this->actingAs($someone)->get("/en/orders/{$order->id}")->assertForbidden();
    }

    // ---- Find my order ----

    public function test_lookup_by_number_and_email_leads_to_the_private_link(): void
    {
        $order = $this->guestOrder();

        foreach (['#' . $order->number, $order->number, (string) $order->id] as $number) {
            $this->post('/nl/orders/lookup', ['number' => $number, 'email' => ' fatima@example.com '])
                ->assertRedirect($order->privateUrl('nl'));
        }
    }

    public function test_lookup_gives_the_same_answer_for_a_wrong_number_or_email(): void
    {
        $order = $this->guestOrder();

        $wrongEmail = $this->from('/en/orders/lookup')->post('/en/orders/lookup', ['number' => $order->number, 'email' => 'other@example.com']);
        $wrongNumber = $this->from('/en/orders/lookup')->post('/en/orders/lookup', ['number' => '999999', 'email' => 'fatima@example.com']);

        $wrongEmail->assertRedirect('/en/orders/lookup')->assertSessionHasErrors('number');
        $this->assertSame(
            'We could not find an order with this number and email address.',
            session('errors')->first('number'),
        );
        $wrongNumber->assertSessionHasErrors(['number' => 'We could not find an order with this number and email address.']);
    }

    public function test_lookup_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/en/orders/lookup', ['number' => '1', 'email' => 'x@example.com']);
        }

        $this->post('/en/orders/lookup', ['number' => '1', 'email' => 'x@example.com'])->assertStatus(429);
    }

    // ---- Accounts and admin ----

    public function test_logged_in_checkout_still_goes_to_the_account_confirmation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/en/cart', ['product_id' => $this->product->id]);

        $this->actingAs($user)->post('/en/checkout', $this->checkoutData())
            ->assertRedirect(route('orders.confirmation', ['locale' => 'en', 'order' => Order::sole()]));
        $this->assertSame($user->id, Order::sole()->user_id);
    }

    public function test_deleting_a_customer_account_keeps_their_orders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/en/cart', ['product_id' => $this->product->id]);
        $this->actingAs($user)->post('/en/checkout', $this->checkoutData());

        $user->delete();

        $this->assertNull(Order::sole()->user_id);
    }

    public function test_admin_sees_guest_orders_with_the_checkout_details(): void
    {
        $order = $this->guestOrder();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/en/admin/orders')->assertOk()->assertSee('Fatima')->assertSee('Fatima@Example.com');
        $this->actingAs($admin)->get("/en/admin/orders/{$order->id}")->assertOk()->assertSee('Fatima@Example.com');
    }
}
