<?php

namespace Tests\Feature;

use App\Mail\NewOrderNotification;
use App\Mail\OrderConfirmation;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Mollie is simulated with Http::fake(): every request the shop sends is
 * recorded, and $this->payments holds what the "Mollie API" answers.
 */
class MolliePaymentTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    /** @var array<string, array> fake Mollie payments by id */
    private array $payments = [];

    private int $nextId = 1;

    private bool $mollieDown = false;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config([
            'services.mollie.key' => 'test_dummykey',
            'services.mollie.api_url' => 'https://api.mollie.test/v2',
            'shop.shipping.free_threshold' => 1000,
            'shop.shipping.flat_rate' => 4.95,
        ]);

        $this->product = Product::factory()->for(Category::factory())->create([
            'name' => 'Black Abaya', 'price' => 49.5, 'stock' => 5, 'is_active' => true,
        ]);

        Http::fake(function (Request $request) {
            if ($this->mollieDown) {
                return Http::response(['detail' => 'unavailable'], 503);
            }
            if ($request->method() === 'POST' && str_ends_with($request->url(), '/payments')) {
                $id = 'tr_test' . $this->nextId++;
                $this->payments[$id] = [
                    'id' => $id, 'status' => 'open', 'metadata' => $request['metadata'],
                    '_links' => ['checkout' => ['href' => "https://www.mollie.com/checkout/{$id}"]],
                ];

                return Http::response($this->payments[$id], 201);
            }
            if ($request->method() === 'GET' && preg_match('#/payments/(tr_\w+)$#', $request->url(), $m)) {
                return isset($this->payments[$m[1]])
                    ? Http::response($this->payments[$m[1]])
                    : Http::response(['detail' => 'not found'], 404);
            }

            return Http::response([], 500);
        });
    }

    private function orderOnline(string $locale = 'nl')
    {
        $this->postJson("/{$locale}/cart", ['product_id' => $this->product->id, 'quantity' => 2])->assertOk();

        return $this->post("/{$locale}/checkout", [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'mollie',
        ]);
    }

    private function mollieSays(string $id, string $status): void
    {
        $this->payments[$id]['status'] = $status;
    }

    private function webhook(string $id)
    {
        return $this->post('/payments/mollie/webhook', ['id' => $id]);
    }

    // ---- Offering online payment ----

    public function test_online_payment_is_offered_only_when_a_mollie_key_is_set(): void
    {
        $this->postJson('/en/cart', ['product_id' => $this->product->id]);
        $this->get('/en/checkout')->assertSee('value="mollie"', false)->assertSee('value="cod"', false);

        config(['services.mollie.key' => null]);
        $this->get('/en/checkout')->assertDontSee('value="mollie"', false)->assertSee('value="cod"', false);
    }

    public function test_mollie_cannot_be_chosen_without_a_key(): void
    {
        config(['services.mollie.key' => null]);

        $this->orderOnline()->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
    }

    // ---- Starting a payment ----

    public function test_checkout_creates_a_mollie_payment_and_sends_the_customer_to_it(): void
    {
        $response = $this->orderOnline('ar');

        $order = Order::sole();
        $response->assertRedirect('https://www.mollie.com/checkout/tr_test1');

        Http::assertSent(function (Request $request) use ($order) {
            return $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer test_dummykey')
                && $request['amount'] === ['currency' => 'EUR', 'value' => '103.95']   // 2 × 49.50 + 4.95
                && $request['metadata'] === ['order_id' => $order->id]
                && $request['locale'] === 'en_GB'                                     // no Arabic at Mollie
                && $request['redirectUrl'] === route('payments.return', ['locale' => 'ar', 'order' => $order->id, 'token' => $order->access_token])
                && $request['webhookUrl'] === route('payments.webhook');
        });

        $this->assertSame('tr_test1', $order->payment_reference);
        $this->assertSame('open', $order->payment_status);
        $this->assertSame('pending', $order->status);
        $this->assertSame(3, $this->product->fresh()->stock);   // reserved while paying
        $this->assertEmpty(session('cart.items'));
        Mail::assertNothingSent();                                // only once paid
    }

    public function test_if_mollie_is_down_the_order_is_cancelled_and_the_cart_kept(): void
    {
        $this->mollieDown = true;

        $this->orderOnline()->assertRedirect(route('checkout.index', ['locale' => 'nl']))->assertSessionHas('error');

        $order = Order::sole();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame(5, $this->product->fresh()->stock);
        $this->assertSame([(string) $this->product->id => 2], session('cart.items'));
        Mail::assertNothingSent();
    }

    // ---- Webhook ----

    public function test_paid_webhook_marks_the_order_paid_and_sends_the_emails_once(): void
    {
        $this->orderOnline();
        $this->mollieSays('tr_test1', 'paid');

        $this->webhook('tr_test1')->assertOk();
        $this->webhook('tr_test1')->assertOk(); // Mollie may call more than once

        $order = Order::sole();
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        Mail::assertSent(OrderConfirmation::class, 1);
        Mail::assertSent(NewOrderNotification::class, 1);
        (new OrderConfirmation($order))->assertSeeInHtml('Online betaald');
    }

    public function test_the_webhook_trusts_the_api_not_the_post(): void
    {
        $this->orderOnline();
        // Mollie still says "open": a forged "paid" post changes nothing.
        $this->post('/payments/mollie/webhook', ['id' => 'tr_test1', 'status' => 'paid'])->assertOk();

        $this->assertSame('open', Order::sole()->payment_status);
    }

    public function test_webhook_ignores_junk_and_asks_for_a_retry_when_mollie_is_unreachable(): void
    {
        $this->webhook('not-a-payment-id')->assertOk();
        $this->webhook('tr_unknown')->assertOk(); // Mollie: 404, not ours -> no pointless retries

        $this->orderOnline();
        $this->mollieDown = true;
        $this->webhook('tr_test1')->assertStatus(503);
    }

    public function test_an_expired_payment_cancels_the_order_and_frees_the_stock(): void
    {
        $this->orderOnline();
        $this->mollieSays('tr_test1', 'expired');

        $this->webhook('tr_test1')->assertOk();

        $order = Order::sole();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('expired', $order->payment_status);
        $this->assertSame(5, $this->product->fresh()->stock);
        Mail::assertNothingSent();
    }

    public function test_a_payment_that_arrives_after_expiry_still_counts(): void
    {
        $this->orderOnline();
        $this->mollieSays('tr_test1', 'expired');
        $this->webhook('tr_test1');

        $this->mollieSays('tr_test1', 'paid');
        $this->webhook('tr_test1');

        $order = Order::sole();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('pending', $order->status);           // back in the queue
        $this->assertSame(3, $this->product->fresh()->stock);   // items taken again
    }

    // ---- Coming back / retrying ----

    public function test_return_page_for_a_paid_order_shows_the_confirmation(): void
    {
        $this->orderOnline();
        $order = Order::sole();
        $this->mollieSays('tr_test1', 'paid');   // webhook not received yet: the return page checks itself

        $this->get(route('payments.return', ['locale' => 'nl', 'order' => $order->id, 'token' => $order->access_token]))
            ->assertRedirect(route('orders.placed', ['locale' => 'nl', 'order' => $order->id, 'token' => $order->access_token]));
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_return_page_while_the_bank_is_still_confirming(): void
    {
        $this->orderOnline();
        $order = Order::sole();
        $this->mollieSays('tr_test1', 'pending');

        $this->get(route('payments.return', ['locale' => 'en', 'order' => $order->id, 'token' => $order->access_token]))
            ->assertOk()
            ->assertSee('Checking your payment')
            ->assertSee('<meta http-equiv="refresh" content="5">', false)
            ->assertDontSee('Try the payment again');
    }

    public function test_after_a_failed_payment_the_customer_can_try_again(): void
    {
        $this->orderOnline();
        $order = Order::sole();
        $this->mollieSays('tr_test1', 'canceled');

        $this->get(route('payments.return', ['locale' => 'en', 'order' => $order->id, 'token' => $order->access_token]))
            ->assertOk()->assertSee('Payment not completed')->assertSee('Try the payment again');

        $this->post(route('payments.retry', ['locale' => 'en', 'order' => $order->id, 'token' => $order->access_token]))
            ->assertRedirect('https://www.mollie.com/checkout/tr_test2');
        $this->assertSame('tr_test2', $order->fresh()->payment_reference);

        // The old attempt's late "canceled" must not undo the new one…
        $this->webhook('tr_test1');
        $this->assertSame('open', $order->fresh()->payment_status);

        // …and the new one being paid completes the order.
        $this->mollieSays('tr_test2', 'paid');
        $this->webhook('tr_test2');
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_an_expired_order_cannot_be_retried(): void
    {
        $this->orderOnline();
        $order = Order::sole();
        $this->mollieSays('tr_test1', 'expired');
        $this->webhook('tr_test1');

        $url = route('payments.return', ['locale' => 'en', 'order' => $order->id, 'token' => $order->access_token]);
        $this->get($url)->assertOk()->assertSee('Order cancelled')->assertDontSee('Try the payment again');
        $this->post(route('payments.retry', ['locale' => 'en', 'order' => $order->id, 'token' => $order->access_token]))->assertRedirect($url);
        $this->assertCount(1, $this->payments); // no new payment created
    }

    public function test_payment_pages_need_the_order_token(): void
    {
        $this->orderOnline();
        $order = Order::sole();
        $wrong = str_repeat('x', 40);

        $this->get("/en/payment/return/{$order->id}/{$wrong}")->assertNotFound();
        $this->post("/en/payment/retry/{$order->id}/{$wrong}")->assertNotFound();
    }

    // ---- Pay on delivery and texts ----

    public function test_pay_on_delivery_is_unchanged(): void
    {
        $this->postJson('/en/cart', ['product_id' => $this->product->id]);
        $this->post('/en/checkout', [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect();

        Http::assertNothingSent();
        Mail::assertSent(OrderConfirmation::class, 1);
    }

    public function test_store_texts_follow_the_payment_methods_offered(): void
    {
        $this->get('/en/faq')->assertSee('You can pay online')->assertSee('or pay on delivery');

        config(['shop.payments.cod_enabled' => false]);
        $this->get('/en/faq')->assertSee('You pay online on a secure page');

        config(['services.mollie.key' => null, 'shop.payments.cod_enabled' => true]);
        $this->get('/en/faq')->assertSee('At the moment you pay on delivery.');
    }
}
