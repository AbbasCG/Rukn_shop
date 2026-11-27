<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Vat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VatTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['shop.vat.rate' => 21, 'shop.shipping.free_threshold' => 50, 'shop.shipping.flat_rate' => 4.95]);
        $this->product = Product::factory()->for(Category::factory())->create(['price' => 40, 'stock' => 5, 'is_active' => true]);
    }

    private function checkout(): Order
    {
        $this->postJson('/en/cart', ['product_id' => $this->product->id]);
        $this->post('/en/checkout', [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect();

        return Order::sole();
    }

    public function test_vat_included_in_an_amount(): void
    {
        $this->assertSame(21.0, Vat::included(121.0));
        $this->assertSame(7.80, Vat::included(44.95));
        $this->assertSame(0.0, Vat::included(100.0, 0));
        $this->assertSame('21', Vat::rateLabel(21.0));
        $this->assertSame('5.5', Vat::rateLabel(5.5));
    }

    public function test_product_page_says_incl_vat_excl_shipping(): void
    {
        $this->get('/en/products/' . $this->product->slug)->assertOk()
            ->assertSee('Incl. 21% VAT,')
            ->assertSee('excl. shipping')
            ->assertSee(route('faq', ['locale' => 'en']) . '#faq-shipping', false);

        $this->get('/nl/products/' . $this->product->slug)->assertSee('Incl. 21% btw,')->assertSee('excl. verzendkosten');
        $this->get('/ar/products/' . $this->product->slug)->assertSee('شامل ضريبة القيمة المضافة 21%');
    }

    public function test_cart_and_checkout_show_the_vat_in_the_total(): void
    {
        $this->postJson('/en/cart', ['product_id' => $this->product->id]);
        // 40.00 + 4.95 shipping = 44.95, of which 7.80 VAT

        $this->get('/en/cart')->assertSee('Total (incl. VAT)')->assertSee('Of which VAT (21%)')->assertSee('€7.80');
        $this->get('/en/checkout')->assertSee('Total (incl. VAT)')->assertSee('€7.80');
    }

    public function test_the_order_stores_rate_and_amount_and_shows_them(): void
    {
        $order = $this->checkout();

        $this->assertSame('21.00', $order->vat_rate);
        $this->assertSame('7.80', $order->vat_amount);
        $this->assertSame('44.95', (string) $order->total);

        $this->get($order->privateUrl('en'))->assertSee('Of which VAT (21%): €7.80');

        Mail::assertQueued(OrderConfirmation::class, function (OrderConfirmation $mail) {
            $mail->assertSeeInHtml('Of which VAT (21%)')->assertSeeInHtml('€7.80')->assertSeeInText('Of which VAT (21%): €7.80');

            return true;
        });
    }

    public function test_a_later_rate_change_does_not_alter_existing_orders(): void
    {
        $order = $this->checkout();
        config(['shop.vat.rate' => 9]);

        $this->get($order->privateUrl('en'))->assertSee('Of which VAT (21%): €7.80');
    }

    public function test_admin_order_page_shows_the_real_vat_and_no_fake_discount(): void
    {
        $order = $this->checkout();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get("/en/admin/orders/{$order->id}")->assertOk()
            ->assertSee('Of which VAT (21%)')->assertSee('€7.80')
            ->assertDontSee('-€0.00');
    }

    public function test_older_orders_without_stored_vat_show_no_vat_line(): void
    {
        $order = $this->checkout();
        $order->update(['vat_rate' => null, 'vat_amount' => null]);

        $this->get($order->privateUrl('en'))->assertDontSee('Of which VAT');
    }

    public function test_terms_state_prices_include_vat(): void
    {
        $this->get('/en/terms-of-service')->assertSee('All prices are in euros and include 21% VAT');
        $this->get('/nl/terms-of-service')->assertSee('inclusief 21% btw');
    }
}
