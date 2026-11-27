<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Invoices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CouponsTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['shop.shipping.free_threshold' => 50, 'shop.shipping.flat_rate' => 4.95, 'shop.vat.rate' => 21]);
        $this->product = Product::factory()->for(Category::factory())->create(['name' => 'Gold Mabkhara', 'price' => 30, 'stock' => 10, 'is_active' => true]);
    }

    private function addToCart(int $quantity = 1): void
    {
        $this->postJson('/en/cart', ['product_id' => $this->product->id, 'quantity' => $quantity])->assertOk();
    }

    private function applyCode(string $code)
    {
        return $this->from('/en/cart')->post('/en/cart/coupon', ['code' => $code]);
    }

    private function checkout()
    {
        return $this->post('/en/checkout', [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ]);
    }

    // ---- Applying a code ----

    public function test_a_free_shipping_code_removes_the_shipping_cost(): void
    {
        Coupon::create(['code' => 'GRATIS', 'is_active' => true]);
        $this->addToCart();                                       // €30: normally €4.95 shipping

        $this->get('/en/cart')->assertSee('€4.95')->assertSee('€34.95');

        $this->applyCode(' gratis ')->assertRedirect('/en/cart')->assertSessionHas('success', 'Code GRATIS applied.');

        $this->get('/en/cart')->assertSee('Free (code GRATIS)')->assertSee('€30.00')->assertDontSee('€34.95')
            ->assertSee('Code GRATIS: free shipping')
            ->assertDontSee('to get free shipping');               // threshold hint hidden
        $this->get('/en/checkout')->assertSee('Free (code GRATIS)');
    }

    public function test_unknown_or_inactive_codes_are_refused(): void
    {
        Coupon::create(['code' => 'OLD', 'is_active' => false]);
        $this->addToCart();

        $this->applyCode('NOPE')->assertSessionHasErrors(['code' => 'This code does not exist or is no longer valid.'], null, 'coupon');
        $this->applyCode('OLD')->assertSessionHasErrors('code', null, 'coupon');
        $this->get('/en/cart')->assertSee('€34.95');
    }

    public function test_minimum_order_amount(): void
    {
        Coupon::create(['code' => 'VANAF40', 'min_order_amount' => 40, 'is_active' => true]);
        $this->addToCart();                                       // €30, below €40

        $this->applyCode('VANAF40');
        $this->get('/en/cart')->assertSee('Code VANAF40 gives free shipping from €40.00 in products.')->assertSee('€34.95');

        $this->addToCart();                                       // now €60
        $this->get('/en/cart')->assertSee('Free (code VANAF40)');
    }

    public function test_remove_the_code(): void
    {
        Coupon::create(['code' => 'GRATIS', 'is_active' => true]);
        $this->addToCart();
        $this->applyCode('GRATIS');

        $this->from('/en/cart')->delete('/en/cart/coupon')->assertRedirect('/en/cart');

        $this->get('/en/cart')->assertSee('€34.95')->assertDontSee('GRATIS');
    }

    public function test_logged_in_customers_can_remove_the_code_too(): void
    {
        // DELETE /cart/{cart} (remove an item) must not swallow /cart/coupon.
        Coupon::create(['code' => 'GRATIS', 'is_active' => true]);
        $this->actingAs(User::factory()->create());
        $this->addToCart();
        $this->applyCode('GRATIS');

        $this->from('/en/cart')->delete('/en/cart/coupon')->assertRedirect('/en/cart')->assertSessionHas('success');
    }

    // ---- Ordering with a code ----

    public function test_the_order_records_the_code_and_what_it_saved(): void
    {
        $coupon = Coupon::create(['code' => 'GRATIS', 'is_active' => true]);
        $this->addToCart();
        $this->applyCode('GRATIS');

        $this->checkout()->assertRedirect();

        $order = Order::sole();
        $this->assertSame('GRATIS', $order->coupon_code);
        $this->assertEquals(0.0, (float) $order->shipping_cost);
        $this->assertEquals(4.95, (float) $order->discount_amount);
        $this->assertEquals(30.0, (float) $order->total);
        $this->assertEquals(5.21, (float) $order->vat_amount);        // VAT on what is paid
        $this->assertSame(1, $coupon->fresh()->uses_count);
        $this->assertNull(session('cart.coupon'));                     // cleared with the cart

        $this->get($order->privateUrl('en'))->assertSee('Free (code GRATIS)');
        Mail::assertQueued(OrderConfirmation::class, function (OrderConfirmation $mail) {
            $mail->assertSeeInHtml('Free (code GRATIS)')->assertSeeInText('Free (code GRATIS)');

            return true;
        });
    }

    public function test_a_code_switched_off_before_checkout_no_longer_applies(): void
    {
        $coupon = Coupon::create(['code' => 'GRATIS', 'is_active' => true]);
        $this->addToCart();
        $this->applyCode('GRATIS');
        $coupon->update(['is_active' => false]);

        $this->checkout();

        $order = Order::sole();
        $this->assertNull($order->coupon_code);
        $this->assertEquals(4.95, (float) $order->shipping_cost);
        $this->assertSame(0, $coupon->fresh()->uses_count);
    }

    public function test_orders_without_a_code_are_unchanged(): void
    {
        $this->addToCart(2);                                      // €60: free by threshold
        $this->checkout();

        $order = Order::sole();
        $this->assertNull($order->coupon_code);
        $this->assertEquals(0.0, (float) $order->discount_amount);
        $this->assertSame('Free', $order->shippingText());
    }

    public function test_invoice_and_export_show_the_code(): void
    {
        Coupon::create(['code' => 'GRATIS', 'is_active' => true]);
        $this->addToCart();
        $this->applyCode('GRATIS');
        $this->checkout();
        $order = Order::sole();
        $order->update(['status' => 'shipped']);
        Invoices::assign($order);

        app()->setLocale('nl');
        $html = view('invoices.pdf', ['order' => $order->fresh()->load('items'), 'business' => config('shop.business')])->render();
        $this->assertStringContainsString('Verzendkosten (kortingscode GRATIS)', $html);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/en/admin/orders/export?status=all')->assertOk();
        $this->actingAs($admin)->get("/en/admin/orders/{$order->id}")->assertSee('Free (code GRATIS)');
    }

    // ---- Admin ----

    public function test_admin_creates_edits_switches_off_and_deletes_codes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post('/en/admin/coupons', ['code' => 'ramadan', 'min_order_amount' => '40', 'note' => 'Instagram', 'is_active' => '1'])
            ->assertRedirect(route('admin.coupons.index', ['locale' => 'en']));
        $coupon = Coupon::sole();
        $this->assertSame('RAMADAN', $coupon->code);
        $this->assertSame('free_shipping', $coupon->type);
        $this->assertEquals(40.0, (float) $coupon->min_order_amount);

        $this->get('/en/admin/coupons')->assertOk()->assertSee('RAMADAN')->assertSee('€40.00')->assertSee('Instagram');

        $this->put("/en/admin/coupons/{$coupon->id}", ['code' => 'RAMADAN', 'min_order_amount' => '']);   // unchecked = off
        $this->assertFalse($coupon->fresh()->is_active);
        $this->assertNull($coupon->fresh()->min_order_amount);

        $this->post('/en/admin/coupons', ['code' => 'RAMADAN'])->assertSessionHasErrors('code');           // duplicate
        $this->post('/en/admin/coupons', ['code' => 'WITH SPACE!'])->assertSessionHasErrors('code');       // spaces removed, "!" not allowed

        $this->delete("/en/admin/coupons/{$coupon->id}")->assertRedirect();
        $this->assertSame(0, Coupon::count());
    }

    public function test_only_admins_manage_codes(): void
    {
        $this->actingAs(User::factory()->create())->get('/en/admin/coupons')->assertForbidden();
        $this->actingAs(User::factory()->create())->post('/en/admin/coupons', ['code' => 'HACK'])->assertForbidden();
        $this->assertSame(0, Coupon::count());
    }

    public function test_translated(): void
    {
        Coupon::create(['code' => 'GRATIS', 'is_active' => true]);
        $this->addToCart();
        $this->post('/ar/cart/coupon', ['code' => 'GRATIS']);

        $this->get('/ar/cart')->assertSee('مجاني (الكود GRATIS)');
        $this->get('/nl/cart')->assertSee('Gratis (code GRATIS)');
    }
}
