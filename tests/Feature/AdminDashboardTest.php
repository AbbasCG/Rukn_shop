<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->for(Category::factory())->create(['stock' => 5]);
        $order = Order::create([
            'status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'cod',
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam', 'country' => 'NL',
            'subtotal' => 40, 'shipping_cost' => 0, 'total' => 40,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'price' => 20]);

        $this->actingAs($admin)->get('/en/admin')->assertOk();
        $this->assertSame(40.0, (float) $order->items()->sum(\DB::raw('price * quantity')));
    }
}
