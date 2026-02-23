<?php

namespace Tests\Feature\Auth;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_items_are_merged_into_user_cart_on_login(): void
    {
        $category = Category::factory()->create();

        $productA = Product::factory()->for($category)->create([
            'is_active' => true,
            'stock' => 10,
        ]);

        $productB = Product::factory()->for($category)->create([
            'is_active' => true,
            'stock' => 10,
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $productA->id,
            'quantity' => 2,
            'status' => 'active',
            'price_at_time' => $productA->price,
        ]);

        $this->withSession([
            'cart.items' => [
                $productA->id => 3,
                $productB->id => 1,
            ],
            'cart.count' => 4,
        ]);

        $response = $this->post(route('login', ['locale' => 'en']), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home', ['locale' => 'en'], false));
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $productA->id,
            'status' => 'active',
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $productB->id,
            'status' => 'active',
            'quantity' => 1,
        ]);

        $this->assertSame(
            2,
            Cart::where('user_id', $user->id)->where('status', 'active')->count()
        );

        $response->assertSessionMissing('cart.items');
        $response->assertSessionMissing('cart.count');
        $response->assertSessionHas('success', __('cart.flash.restored'));
    }

    public function test_unavailable_guest_cart_items_are_skipped_on_login(): void
    {
        $category = Category::factory()->create();

        $availableProduct = Product::factory()->for($category)->create([
            'is_active' => true,
            'stock' => 3,
        ]);

        $inactiveProduct = Product::factory()->for($category)->inactive()->create([
            'stock' => 5,
        ]);

        $outOfStockProduct = Product::factory()->for($category)->outOfStock()->create([
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->withSession([
            'cart.items' => [
                $availableProduct->id => 1,
                $inactiveProduct->id => 2,
                $outOfStockProduct->id => 2,
            ],
            'cart.count' => 5,
        ]);

        $response = $this->post(route('login', ['locale' => 'en']), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home', ['locale' => 'en'], false));
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $availableProduct->id,
            'status' => 'active',
            'quantity' => 1,
        ]);

        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
            'product_id' => $inactiveProduct->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
            'product_id' => $outOfStockProduct->id,
            'status' => 'active',
        ]);

        $response->assertSessionHas('success', __('cart.flash.restored'));
        $response->assertSessionHas('warning', __('cart.flash.restored_partial'));
    }
}
