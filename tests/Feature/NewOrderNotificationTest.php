<?php

namespace Tests\Feature;

use App\Mail\NewOrderNotification;
use App\Mail\OrderConfirmation;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'shop.shipping.flat_rate' => 4.95,
            'shop.shipping.free_threshold' => 50,
            'shop.order_notifications.emails' => ['owner@rukn.nl', 'staff@rukn.nl'],
            'shop.order_notifications.locale' => 'ar',
            'shop.order_notifications.low_stock_threshold' => 5,
        ]);
    }

    private function placeOrder(int $stock = 10, int $quantity = 2, string $locale = 'nl'): Order
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'name' => 'Black Abaya', 'price' => 20, 'stock' => $stock, 'is_active' => true,
        ]);
        Cart::create([
            'user_id' => $user->id, 'product_id' => $product->id,
            'quantity' => $quantity, 'status' => 'active', 'price_at_time' => 20,
        ]);

        $this->actingAs($user)->post("/{$locale}/checkout", [
            'name' => 'Fatima', 'email' => 'fatima@example.com', 'phone' => '06 1234 5678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect();

        return Order::firstOrFail();
    }

    public function test_staff_are_notified_of_every_new_order(): void
    {
        Mail::fake();

        $order = $this->placeOrder();

        Mail::assertQueued(NewOrderNotification::class, function (NewOrderNotification $mail) use ($order) {
            return $mail->hasTo('owner@rukn.nl')
                && $mail->hasTo('staff@rukn.nl')
                && $mail->hasReplyTo('fatima@example.com')
                && $mail->locale === 'ar'
                && $mail->order->is($order);
        });
        Mail::assertQueued(OrderConfirmation::class, 1);
    }

    public function test_notification_content_is_actionable(): void
    {
        $order = $this->placeOrder(locale: 'nl');
        $mail = new NewOrderNotification($order);

        $mail->assertHasSubject('طلب جديد #' . $order->number . " من Fatima — 44.95\u{00A0}€");
        $mail->assertSeeInHtml('dir="rtl"', false);
        $mail->assertSeeInHtml('fatima@example.com');
        $mail->assertSeeInHtml('tel:0612345678', false);
        $mail->assertSeeInHtml('Black Abaya');
        $mail->assertSeeInHtml('Nederlands');                      // language the customer ordered in
        $mail->assertSeeInHtml('/ar/admin/orders/' . $order->id);   // admin link in the staff language
        $mail->assertSeeInText('Black Abaya');
        $mail->assertSeeInHtml('المنتجات');
        $mail->assertDontSeeInHtml('طلبك');                          // customer wording stays out
    }

    public function test_low_stock_is_flagged_only_when_it_applies(): void
    {
        $plenty = $this->placeOrder(stock: 20, quantity: 2);   // 18 left
        $this->assertTrue((new NewOrderNotification($plenty))->lowStockProducts()->isEmpty());
        (new NewOrderNotification($plenty))->assertDontSeeInHtml('تنبيه: مخزون منخفض');

        Order::query()->delete();
        Cart::query()->delete();

        $low = $this->placeOrder(stock: 5, quantity: 2);       // 3 left
        $mail = new NewOrderNotification($low);
        $this->assertCount(1, $mail->lowStockProducts());
        $mail->assertSeeInHtml('تنبيه: مخزون منخفض');
        $mail->assertSeeInHtml('Black Abaya — باقي 3');
    }

    public function test_sold_out_is_reported_as_sold_out(): void
    {
        $order = $this->placeOrder(stock: 2, quantity: 2);     // 0 left

        (new NewOrderNotification($order))->assertSeeInHtml('Black Abaya — نفد المخزون');
    }

    public function test_no_notification_when_no_staff_address_is_configured(): void
    {
        config(['shop.order_notifications.emails' => []]);
        Mail::fake();

        $this->placeOrder();

        Mail::assertNotQueued(NewOrderNotification::class);
        Mail::assertQueued(OrderConfirmation::class, 1);
    }
}
