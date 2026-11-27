<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationStockTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** An order of 3 items whose stock was already taken (5 left). */
    private function order(string $status = 'pending'): array
    {
        $product = Product::factory()->for(Category::factory())->create(['stock' => 5, 'is_active' => true]);
        $order = Order::create([
            'user_id' => $this->admin->id, 'status' => $status, 'payment_status' => 'open', 'payment_method' => 'cod',
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam', 'country' => 'NL',
            'subtotal' => 60, 'shipping_cost' => 0, 'total' => 60,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 3, 'price' => 20]);

        return [$order, $product];
    }

    private function setStatus(Order $order, string $status)
    {
        return $this->actingAs($this->admin)->patch("/en/admin/orders/{$order->id}", ['status' => $status]);
    }

    public function test_cancelling_an_order_puts_the_items_back_in_stock(): void
    {
        [$order, $product] = $this->order();

        $this->setStatus($order, 'cancelled')->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_saving_a_cancelled_order_again_does_not_restock_twice(): void
    {
        [$order, $product] = $this->order();

        $this->setStatus($order, 'cancelled');
        $this->setStatus($order, 'cancelled');

        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_reactivating_a_cancelled_order_takes_the_stock_again(): void
    {
        [$order, $product] = $this->order('cancelled');

        $this->setStatus($order, 'processing')->assertRedirect();

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_reactivation_is_refused_when_stock_is_insufficient(): void
    {
        [$order, $product] = $this->order('cancelled');
        $product->update(['stock' => 2]); // sold in the meantime

        $this->setStatus($order, 'pending')->assertSessionHas('error');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_other_status_changes_leave_stock_alone(): void
    {
        [$order, $product] = $this->order();

        $this->setStatus($order, 'processing');
        $this->setStatus($order, 'shipped');

        $this->assertSame(5, $product->fresh()->stock);
    }
}
