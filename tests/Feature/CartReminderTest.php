<?php

namespace Tests\Feature;

use App\Mail\CartReminder;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CartReminderTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->product = Product::factory()->for(Category::factory())->create(['name' => 'Gold Mabkhara', 'price' => 30, 'stock' => 5, 'is_active' => true]);
        $this->customer = User::factory()->create(['email' => 'f@example.com', 'locale' => 'nl']);
    }

    private function cartLine(?User $user = null): Cart
    {
        return Cart::create(['user_id' => ($user ?? $this->customer)->id, 'product_id' => $this->product->id, 'quantity' => 2, 'status' => 'active', 'price_at_time' => 30]);
    }

    private function run_(): void
    {
        $this->artisan('cart:send-reminders')->assertSuccessful();
    }

    public function test_first_reminder_after_a_day_then_a_second_after_three_days_then_nothing(): void
    {
        $this->cartLine();

        $this->travel(23)->hours();
        $this->run_();
        Mail::assertNothingQueued();

        $this->travel(2)->hours();                      // 25 hours
        $this->run_();
        Mail::assertQueued(CartReminder::class, fn (CartReminder $m) => $m->number === 1 && $m->hasTo('f@example.com') && $m->locale === 'nl');

        $this->run_();                                  // not twice
        Mail::assertQueued(CartReminder::class, 1);

        $this->travel(48)->hours();                     // 73 hours
        $this->run_();
        Mail::assertQueued(CartReminder::class, 2);
        Mail::assertQueued(CartReminder::class, fn (CartReminder $m) => $m->number === 2);

        $this->travel(5)->days();
        $this->run_();
        Mail::assertQueued(CartReminder::class, 2);      // at most two per cart
    }

    public function test_changing_the_cart_starts_over(): void
    {
        $line = $this->cartLine();
        $this->travel(25)->hours();
        $this->run_();
        Mail::assertQueued(CartReminder::class, 1);

        $line->update(['quantity' => 3]);                // a new cart activity
        $this->travel(25)->hours();
        $this->run_();
        Mail::assertQueued(CartReminder::class, fn (CartReminder $m) => $m->number === 1);
        Mail::assertQueued(CartReminder::class, 2);
    }

    public function test_who_does_not_get_one(): void
    {
        $this->cartLine();
        $this->customer->update(['cart_reminders' => false]);                  // turned off
        $admin = User::factory()->create(['role' => 'admin']);
        $this->cartLine($admin);                                                 // staff
        $soldOut = User::factory()->create();
        Cart::create(['user_id' => $soldOut->id, 'product_id' => Product::factory()->for(Category::factory())->create(['stock' => 0, 'is_active' => true])->id, 'quantity' => 1, 'status' => 'active']);

        $this->travel(25)->hours();
        $this->run_();
        Mail::assertNothingQueued();

        $old = User::factory()->create();
        $this->cartLine($old);
        $this->travel(20)->days();                                               // a long-forgotten cart
        $this->run_();
        Mail::assertNothingQueued();
    }

    public function test_switched_off_for_the_whole_shop(): void
    {
        config(['shop.cart_reminders.enabled' => false]);
        $this->cartLine();
        $this->travel(2)->days();
        $this->run_();
        Mail::assertNothingQueued();
    }

    public function test_the_email(): void
    {
        $mail = new CartReminder($this->cartLineOwner(), 1);

        $mail->assertSeeInHtml('Gold Mabkhara')->assertSeeInHtml('Naar mijn winkelwagen')
            ->assertSeeInHtml(route('cart.index', ['locale' => 'nl']))
            ->assertSeeInHtml('Deze e-mails stoppen')
            ->assertSeeInText('2 × ');
        $this->assertSame('Je hebt iets in je winkelwagen laten liggen', $mail->envelope()->subject);
        $this->assertStringContainsString('/nl/cart-reminders/unsubscribe/' . $this->customer->id, $mail->headers()->text['List-Unsubscribe']);
        $this->assertSame('Je winkelwagen wacht nog op je', (new CartReminder($this->customer, 2))->envelope()->subject);
    }

    private function cartLineOwner(): User
    {
        $this->cartLine();

        return $this->customer;
    }

    public function test_unsubscribe_link(): void
    {
        $url = CartReminder::unsubscribeUrl($this->customer, 'en');

        $this->get($url)->assertOk()->assertSee('Do you want to stop the emails');
        $this->assertTrue($this->customer->fresh()->cart_reminders);               // a GET changes nothing

        $this->post($url)->assertOk()->assertSee('you will no longer get cart reminders');
        $this->assertFalse($this->customer->fresh()->cart_reminders);

        $this->get('/en/cart-reminders/unsubscribe/' . $this->customer->id)->assertForbidden();   // unsigned
        $this->post($url . 'x')->assertForbidden();
    }

    public function test_profile_toggle_and_language_remembered(): void
    {
        $this->actingAs($this->customer);
        $this->get('/en/profile')->assertSee('Email me a reminder when I leave items in my cart');

        $this->patch('/en/profile', ['name' => 'F', 'email' => 'f@example.com'])->assertSessionHasNoErrors();
        $this->assertFalse($this->customer->fresh()->cart_reminders);
        $this->patch('/en/profile', ['name' => 'F', 'email' => 'f@example.com', 'cart_reminders' => '1']);
        $this->assertTrue($this->customer->fresh()->cart_reminders);

        $this->postJson('/ar/cart', ['product_id' => $this->product->id])->assertOk();
        $this->assertSame('ar', $this->customer->fresh()->locale);
    }

    public function test_privacy_policy_mentions_it(): void
    {
        $this->get('/en/privacy-policy')->assertSee('we may email you up to two reminders');
    }
}
