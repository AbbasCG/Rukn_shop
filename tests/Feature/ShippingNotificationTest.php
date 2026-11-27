<?php

namespace Tests\Feature;

use App\Mail\OrderShipped;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ShippingNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['shop.contact_email' => 'info@rukn.nl']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function order(array $attributes = []): Order
    {
        $product = Product::factory()->for(Category::factory())->create(['name' => 'Black Abaya', 'stock' => 5]);
        $order = Order::create($attributes + [
            'status' => 'processing', 'payment_status' => 'open', 'payment_method' => 'cod', 'locale' => 'nl',
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234 ab', 'city' => 'Amsterdam', 'country' => 'Nederland',
            'subtotal' => 49, 'shipping_cost' => 4.95, 'total' => 53.95,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'Black Abaya', 'quantity' => 1, 'price' => 49]);

        return $order;
    }

    private function update(Order $order, array $data)
    {
        return $this->actingAs($this->admin)->patch("/en/admin/orders/{$order->id}", $data);
    }

    public function test_marking_an_order_shipped_emails_the_customer_with_the_tracking_link(): void
    {
        $order = $this->order();

        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'postnl', 'tracking_number' => '3s abcd 1234567'])
            ->assertRedirect()->assertSessionHas('success', 'The shipping email to f@example.com is being sent.');

        $order->refresh();
        $this->assertNotNull($order->shipped_at);
        $this->assertSame('3S ABCD 1234567', $order->tracking_number);
        $this->assertSame('https://jouw.postnl.nl/track-and-trace/3SABCD1234567-NL-1234AB', $order->trackingUrl());

        Mail::assertQueued(OrderShipped::class, function (OrderShipped $mail) use ($order) {
            $mail->assertSeeInHtml('https://jouw.postnl.nl/track-and-trace/3SABCD1234567-NL-1234AB');

            return $mail->hasTo('f@example.com') && $mail->locale === 'nl'
                && $mail->hasReplyTo('info@rukn.nl')
                && $mail->hasSubject('Je bestelling #' . $order->number . ' is onderweg');
        });
        Mail::assertQueuedCount(1);
    }

    public function test_the_email_is_sent_only_once(): void
    {
        $order = $this->order();
        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'dhl', 'tracking_number' => 'JVGL123']);
        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'dhl', 'tracking_number' => 'JVGL123']);   // saved again
        $this->update($order, ['status' => 'processing', 'tracking_carrier' => 'dhl', 'tracking_number' => 'JVGL123']); // back…
        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'dhl', 'tracking_number' => 'JVGL123']);   // …and shipped again
        $this->update($order, ['status' => 'delivered', 'tracking_carrier' => 'dhl', 'tracking_number' => 'JVGL123']);

        Mail::assertQueuedCount(1);
    }

    public function test_a_new_tracking_number_after_shipping_sends_the_new_details(): void
    {
        $order = $this->order();
        $this->update($order, ['status' => 'shipped']);   // shipped without tracking first

        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'dpd', 'tracking_number' => '0150 1234']);

        Mail::assertQueuedCount(2);
        Mail::assertQueued(OrderShipped::class, fn (OrderShipped $mail) => $mail->trackingUpdate
            && $mail->hasSubject('Nieuwe track & trace voor bestelling #' . $order->number));
    }

    public function test_shipping_without_tracking_says_so(): void
    {
        $order = $this->order(['locale' => 'ar']);

        $this->update($order, ['status' => 'shipped']);

        Mail::assertQueued(OrderShipped::class, function (OrderShipped $mail) {
            $mail->assertSeeInHtml('هذا الطرد مُرسل بدون رقم تتبّع.')->assertSeeInHtml('dir="rtl"', false)
                ->assertDontSeeInHtml('تتبّع طردك');

            return true;
        });
    }

    public function test_pay_on_delivery_reminder_and_text_version(): void
    {
        $order = $this->order(['locale' => 'en']);
        $order->update(['tracking_carrier' => 'ups', 'tracking_number' => '1Z999', 'shipped_at' => now(), 'status' => 'shipped']);

        $mail = new OrderShipped($order->fresh());
        $mail->assertSeeInHtml('You pay €53.95 when your order is delivered.')
            ->assertSeeInText('UPS tracking number: 1Z999')
            ->assertSeeInText('https://www.ups.com/track?loc=nl_NL&tracknum=1Z999')
            ->assertSeeInText('Black Abaya');
    }

    public function test_a_custom_tracking_link_wins_and_other_carrier_needs_one(): void
    {
        $order = $this->order(['tracking_carrier' => 'other', 'tracking_number' => 'ABC123']);
        $this->assertNull($order->trackingUrl());

        $order->tracking_url = 'https://track.example.com/ABC123';
        $this->assertSame('https://track.example.com/ABC123', $order->trackingUrl());
    }

    public function test_validation(): void
    {
        $order = $this->order();

        $this->update($order, ['status' => 'shipped', 'tracking_number' => 'ABC123'])->assertSessionHasErrors('tracking_carrier');
        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'postnl', 'tracking_number' => 'abc<script>'])->assertSessionHasErrors('tracking_number');
        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'fedex', 'tracking_number' => 'ABC'])->assertSessionHasErrors('tracking_carrier');
        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'other', 'tracking_number' => 'ABC', 'tracking_url' => 'javascript:alert(1)'])->assertSessionHasErrors('tracking_url');

        $this->assertNull($order->fresh()->shipped_at);
        Mail::assertNothingOutgoing();
    }

    public function test_a_mail_failure_keeps_the_update_and_warns_the_admin(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));
        $order = $this->order();

        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'postnl', 'tracking_number' => '3S1'])
            ->assertSessionHas('warning');

        $this->assertSame('shipped', $order->fresh()->status);
    }

    public function test_the_customer_sees_the_tracking_on_the_order_page(): void
    {
        $order = $this->order();
        $this->update($order, ['status' => 'shipped', 'tracking_carrier' => 'postnl', 'tracking_number' => '3SABC']);
        $order->refresh();

        $this->get($order->privateUrl('en'))->assertOk()
            ->assertSee('PostNL tracking number')
            ->assertSee('3SABC')
            ->assertSee('href="https://jouw.postnl.nl/track-and-trace/3SABC-NL-1234AB"', false);
    }

    public function test_nothing_is_shown_before_shipping(): void
    {
        $order = $this->order();

        $this->get($order->privateUrl('en'))->assertOk()->assertDontSee('Track your parcel')->assertDontSee('Shipped on');
    }

    public function test_admin_order_page_has_the_tracking_form(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin)->get("/en/admin/orders/{$order->id}")->assertOk()
            ->assertSee('name="tracking_number"', false)
            ->assertSee('<option value="postnl"', false)
            ->assertSee('When you first set the status to Shipped');
    }

    public function test_country_codes_from_typed_countries(): void
    {
        foreach (['NL' => 'Nederland', 'BE' => 'België', 'DE' => 'Germany', 'FR' => 'fr', 'NL ' => 'هولندا'] as $code => $typed) {
            $this->assertSame(trim($code), (new Order(['country' => $typed]))->countryCode(), $typed);
        }
    }
}
