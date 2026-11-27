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

class OrderItemsSurviveProductDeletionTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->customer = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->product = Product::factory()->for(Category::factory())->create([
            'name' => 'Black Abaya', 'slug' => 'black-abaya', 'price' => 49, 'stock' => 5, 'is_active' => true,
        ]);
    }

    private function placeOrder(): Order
    {
        Cart::create([
            'user_id' => $this->customer->id, 'product_id' => $this->product->id,
            'quantity' => 2, 'status' => 'active', 'price_at_time' => 49,
        ]);

        $this->actingAs($this->customer)->post('/en/checkout', [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect();

        return Order::with('items')->sole();
    }

    public function test_checkout_records_the_product_name_on_the_order_line(): void
    {
        $order = $this->placeOrder();

        $this->assertSame('Black Abaya', $order->items->first()->product_name);
    }

    public function test_deleting_a_product_keeps_it_in_past_orders(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->admin)->delete('/en/admin/products/black-abaya')->assertRedirect();

        $this->assertModelMissing($this->product);
        $item = $order->items()->sole();
        $this->assertNull($item->product_id);
        $this->assertSame('Black Abaya', $item->display_name);
        $this->assertSame(2, (int) $item->quantity);
        $this->assertEqualsWithDelta(49, (float) $item->price, 0.001);
    }

    public function test_order_pages_and_email_still_work_after_the_product_is_deleted(): void
    {
        $order = $this->placeOrder();
        $this->product->delete();

        $this->actingAs($this->customer)->get("/en/orders/{$order->id}")->assertOk()->assertSee('Black Abaya');
        $this->actingAs($this->customer)->get("/en/orders/confirmation/{$order->id}")->assertOk()->assertSee('Black Abaya');
        $this->actingAs($this->admin)->get("/en/admin/orders/{$order->id}")->assertOk()->assertSee('Black Abaya');

        (new OrderConfirmation($order->fresh()))->assertSeeInHtml('Black Abaya');
    }

    public function test_orders_show_the_name_as_ordered_after_a_rename(): void
    {
        $order = $this->placeOrder();
        $this->product->update(['name' => 'Classic Black Abaya']);

        $this->actingAs($this->customer)->get("/en/orders/{$order->id}")
            ->assertOk()->assertSee('Black Abaya')->assertDontSee('Classic Black Abaya');
    }

    public function test_order_pages_no_longer_show_a_meaningless_sku(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->customer)->get("/en/orders/{$order->id}")->assertOk()->assertDontSee('N/A');
    }

    public function test_cancelling_an_order_whose_product_was_deleted_still_works(): void
    {
        $order = $this->placeOrder();
        $this->product->delete();

        $this->actingAs($this->admin)->patch("/en/admin/orders/{$order->id}", ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_admin_can_find_orders_by_product_name(): void
    {
        $order = $this->placeOrder();
        $this->product->delete();
        $other = Order::create([
            'user_id' => $this->customer->id, 'status' => 'pending', 'payment_status' => 'open', 'payment_method' => 'cod',
            'name' => 'Someone', 'email' => 'other@example.com', 'phone' => '0600000000',
            'address_line1' => 'Straat 2', 'postal_code' => '1000AA', 'city' => 'Utrecht', 'country' => 'NL',
            'subtotal' => 10, 'shipping_cost' => 0, 'total' => 10,
        ]);
        $other->items()->create(['product_name' => 'Electric Mabkhara', 'quantity' => 1, 'price' => 10]);

        $this->actingAs($this->admin)->get('/en/admin/orders?q=Abaya')
            ->assertOk()
            ->assertSee(route('admin.orders.show', ['locale' => 'en', 'order' => $order]), false)
            ->assertDontSee(route('admin.orders.show', ['locale' => 'en', 'order' => $other]), false);
    }
}
