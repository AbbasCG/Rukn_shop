<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartEditTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = Product::factory()->for(Category::factory())->create(['name' => 'Gold Mabkhara', 'price' => 30, 'stock' => 5, 'is_active' => true]);
    }

    private function add(Product $product, ?int $variantId = null, int $quantity = 1): void
    {
        $this->postJson('/en/cart', ['product_id' => $product->id, 'variant_id' => $variantId, 'quantity' => $quantity])->assertOk();
    }

    public function test_guest_sees_quantity_and_remove_controls(): void
    {
        $this->add($this->product);

        $this->get('/en/cart')->assertOk()
            ->assertSee('action="' . route('cart.update', ['locale' => 'en', 'line' => (string) $this->product->id]) . '"', false)
            ->assertSee('action="' . route('cart.destroy', ['locale' => 'en', 'line' => (string) $this->product->id]) . '"', false)
            ->assertSee('action="' . route('cart.clear', ['locale' => 'en']) . '"', false);
    }

    public function test_guest_changes_the_quantity_capped_at_stock(): void
    {
        $this->add($this->product);
        $key = (string) $this->product->id;

        $this->from('/en/cart')->patch("/en/cart/{$key}", ['quantity' => 3])->assertRedirect('/en/cart')->assertSessionHas('success');
        $this->assertSame(3, session('cart.items')[$key]);
        $this->assertSame(3, session('cart.count'));

        $this->from('/en/cart')->patch("/en/cart/{$key}", ['quantity' => 9])->assertSessionHas('warning');
        $this->assertSame(5, session('cart.items')[$key]);

        $this->get('/en/cart')->assertSee('€150.00');
    }

    public function test_guest_updates_a_variant_line_and_gets_json_totals(): void
    {
        $abaya = Product::factory()->for(Category::factory())->create(['price' => 60, 'stock' => 4, 'is_active' => true]);
        $variant = ProductVariant::create(['product_id' => $abaya->id, 'size' => '54', 'stock' => 4]);
        $this->add($abaya, $variant->id);
        $key = "{$abaya->id}:{$variant->id}";

        $this->get('/en/cart')->assertSee(route('cart.update', ['locale' => 'en', 'line' => $key]), false);

        $this->patchJson("/en/cart/{$key}", ['quantity' => 2])->assertOk()
            ->assertJson(['ok' => true, 'quantity' => 2, 'subtotal' => 120]);
    }

    public function test_guest_removes_a_line_and_clears_the_cart(): void
    {
        $other = Product::factory()->for(Category::factory())->create(['price' => 10, 'stock' => 5, 'is_active' => true]);
        $this->add($this->product);
        $this->add($other);

        $this->from('/en/cart')->delete("/en/cart/{$this->product->id}")->assertRedirect('/en/cart')->assertSessionHas('success');
        $this->assertSame([(string) $other->id => 1], array_map('intval', session('cart.items')));
        $this->assertSame(1, session('cart.count'));

        $this->from('/en/cart')->delete('/en/cart')->assertRedirect('/en/cart');
        $this->assertSame([], session('cart.items'));
        $this->get('/en/cart')->assertSee(__('cart.empty.title', [], 'en'));
    }

    public function test_guest_cannot_edit_a_line_that_is_not_in_their_cart(): void
    {
        $this->add($this->product);

        $this->patch('/en/cart/999', ['quantity' => 2])->assertNotFound();
        $this->delete('/en/cart/999')->assertNotFound();
        $this->patch('/en/cart/abc', ['quantity' => 2])->assertNotFound();
        $this->assertSame(1, session('cart.items')[(string) $this->product->id]);
    }

    public function test_logged_in_customer_edits_only_their_own_lines(): void
    {
        $owner = User::factory()->create();
        $line = Cart::create(['user_id' => $owner->id, 'product_id' => $this->product->id, 'quantity' => 1, 'status' => 'active', 'price_at_time' => 30]);

        $this->actingAs($owner)->patch("/en/cart/{$line->id}", ['quantity' => 2])->assertSessionHas('success');
        $this->assertSame(2, (int) $line->fresh()->quantity);

        $this->actingAs(User::factory()->create())->patch("/en/cart/{$line->id}", ['quantity' => 4])->assertForbidden();
        $this->actingAs(User::factory()->create())->delete("/en/cart/{$line->id}")->assertForbidden();
        $this->assertSame(2, (int) $line->fresh()->quantity);

        $this->actingAs($owner)->delete("/en/cart/{$line->id}")->assertRedirect();
        $this->assertSame(0, Cart::count());
    }

    public function test_coupon_routes_still_work_next_to_the_line_routes(): void
    {
        $this->add($this->product);

        $this->from('/en/cart')->delete('/en/cart/coupon')->assertRedirect('/en/cart');
        $this->assertSame(1, session('cart.items')[(string) $this->product->id]);
    }
}
