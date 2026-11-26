<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'shop.shipping.flat_rate' => 4.95,
            'shop.shipping.free_threshold' => 50,
            'shop.contact_email' => 'info@rukn.nl',
        ]);
    }

    private function placeOrder(string $locale = 'en', string $email = 'customer@example.com'): Order
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'name' => 'Black Abaya', 'price' => 20, 'stock' => 10, 'is_active' => true,
        ]);
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => 2, 'status' => 'active', 'price_at_time' => 20,
        ]);

        $this->actingAs($user)->post("/{$locale}/checkout", [
            'name' => 'Fatima', 'email' => $email, 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect();

        return Order::firstOrFail();
    }

    public function test_confirmation_is_sent_to_the_checkout_email_in_the_order_language(): void
    {
        Mail::fake();

        $order = $this->placeOrder('ar', 'fatima@example.com');

        $this->assertSame('ar', $order->locale);
        Mail::assertQueued(OrderConfirmation::class, function (OrderConfirmation $mail) use ($order) {
            return $mail->hasTo('fatima@example.com')
                && $mail->locale === 'ar'
                && $mail->hasReplyTo('info@rukn.nl')
                && $mail->order->is($order);
        });
        Mail::assertQueued(OrderConfirmation::class, 1);
    }

    public function test_email_content_matches_the_order(): void
    {
        $order = $this->placeOrder('en');
        $mail = new OrderConfirmation($order);

        $mail->assertHasSubject('Your order #' . $order->number . ' at Rukn');
        $mail->assertSeeInHtml('Black Abaya');
        $mail->assertSeeInHtml('#' . $order->number);
        $mail->assertSeeInHtml('€44.95');          // 2 × €20 + €4.95 shipping
        $mail->assertSeeInHtml('€4.95');
        $mail->assertSeeInHtml('You pay €44.95 when your order is delivered.');
        $mail->assertSeeInHtml($order->privateUrl('en'), false); // private link: works without an account
        $mail->assertSeeInText('Black Abaya');
        $mail->assertDontSeeInText('&#039;');
    }

    public function test_arabic_email_is_right_to_left_and_translated(): void
    {
        $order = $this->placeOrder('ar');
        $mail = new OrderConfirmation($order);

        $mail->assertHasSubject('طلبك رقم #' . $order->number . ' من ركن');
        $mail->assertSeeInHtml('dir="rtl"', false);
        $mail->assertSeeInHtml('شكراً لطلبك!');
        $mail->assertSeeInHtml($order->privateUrl('ar'), false); // private link: works without an account
        $mail->assertDontSeeInHtml(':min_days');
    }

    public function test_dutch_email_uses_dutch_formatting(): void
    {
        $order = $this->placeOrder('nl');
        $mail = new OrderConfirmation($order);

        $mail->assertHasSubject('Je bestelling #' . $order->number . ' bij Rukn');
        $mail->assertSeeInHtml("€\u{00A0}44,95");
        $mail->assertSeeInHtml('Bezorgadres');
    }

    public function test_a_mail_failure_does_not_break_or_undo_the_order(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create(['price' => 20, 'stock' => 10, 'is_active' => true]);
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => 1, 'status' => 'active', 'price_at_time' => 20,
        ]);

        $this->actingAs($user)->post('/en/checkout', [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect(route('orders.confirmation', ['locale' => 'en', 'order' => Order::first()]));

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(9, $product->fresh()->stock);
        $this->assertDatabaseMissing('carts', ['user_id' => $user->id]);
    }
}
