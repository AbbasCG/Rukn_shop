<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use App\Support\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private Product $abaya;

    private Product $mabkhara;

    protected function setUp(): void
    {
        parent::setUp();
        $category = Category::factory()->create();
        $this->abaya = Product::factory()->for($category)->create(['name' => 'Black Abaya', 'price' => 80, 'stock' => 3, 'is_active' => true]);
        $this->mabkhara = Product::factory()->for($category)->create(['name' => 'Gold Mabkhara', 'price' => 30, 'stock' => 3, 'is_active' => true]);
    }

    private function toggle(Product $product)
    {
        return $this->postJson("/en/wishlist/{$product->slug}");
    }

    public function test_a_guest_saves_favourites_in_a_cookie(): void
    {
        $response = $this->toggle($this->abaya)->assertOk()
            ->assertJson(['saved' => true, 'count' => 1, 'message' => 'Saved to your favourites.']);
        $response->assertCookie(Wishlist::COOKIE, (string) $this->abaya->id);

        // The cookie comes back with the next requests.
        $this->withCookie(Wishlist::COOKIE, (string) $this->abaya->id)
            ->get('/en/wishlist')->assertOk()->assertSee('Black Abaya')->assertDontSee('Gold Mabkhara')
            ->assertSee('Saved on this device');

        $this->withCredentials()->withCookie(Wishlist::COOKIE, (string) $this->abaya->id)   // JSON calls send cookies only withCredentials()
            ->postJson("/en/wishlist/{$this->abaya->slug}")
            ->assertJson(['saved' => false, 'count' => 0, 'message' => 'Removed from your favourites.']);
        $this->assertSame(0, WishlistItem::count());
    }

    public function test_customers_keep_them_in_their_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->toggle($this->abaya)->assertJson(['saved' => true, 'count' => 1]);
        $this->toggle($this->mabkhara)->assertJson(['saved' => true, 'count' => 2]);
        $this->toggle($this->abaya)->assertJson(['saved' => false, 'count' => 1]);

        $this->assertSame([$this->mabkhara->id], WishlistItem::where('user_id', $user->id)->pluck('product_id')->all());
        $this->get('/en/wishlist')->assertSee('Gold Mabkhara')->assertDontSee('Black Abaya')->assertDontSee('Saved on this device');
    }

    public function test_guest_favourites_move_into_the_account_on_login(): void
    {
        $user = User::factory()->create(['email' => 'f@example.com', 'password' => 'secret-pass']);
        WishlistItem::create(['user_id' => $user->id, 'product_id' => $this->mabkhara->id]);

        $this->withCookie(Wishlist::COOKIE, $this->abaya->id . ',' . $this->mabkhara->id . ',99999')
            ->post('/en/login', ['email' => 'f@example.com', 'password' => 'secret-pass'])
            ->assertRedirect()
            ->assertCookieExpired(Wishlist::COOKIE);

        $this->assertEqualsCanonicalizing([$this->abaya->id, $this->mabkhara->id], WishlistItem::where('user_id', $user->id)->pluck('product_id')->all());
    }

    public function test_registering_keeps_them_too(): void
    {
        $this->withCookie(Wishlist::COOKIE, (string) $this->abaya->id)->post('/en/register', [
            'name' => 'Mariam', 'email' => 'm@example.com', 'password' => 'Password-123!', 'password_confirmation' => 'Password-123!',
        ]);

        $user = User::where('email', 'm@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame([$this->abaya->id], WishlistItem::where('user_id', $user->id)->pluck('product_id')->all());
    }

    public function test_hearts_on_cards_product_page_and_header(): void
    {
        $this->get('/en/products')->assertOk()->assertSee('Save to favourites')
            ->assertSee('wishlist\\/' . $this->abaya->slug, false);   // inside @js(): slashes escaped
        $this->get("/en/products/{$this->abaya->slug}")->assertOk()->assertSee('Save to favourites');

        $this->actingAs(User::factory()->create());
        $this->toggle($this->abaya);
        $this->get("/en/products/{$this->abaya->slug}")
            ->assertSee('aria-pressed="true"', false)->assertSee('Remove from favourites')
            ->assertSee("Alpine.store('wishlist', { count: 1 })", false)
            ->assertSee(route('wishlist.index', ['locale' => 'en']), false);
    }

    public function test_hidden_products_cannot_be_added_and_are_not_listed(): void
    {
        $this->actingAs($user = User::factory()->create());
        $this->toggle($this->abaya);
        $this->abaya->update(['is_active' => false]);
        $hidden = Product::factory()->for(Category::factory())->create(['is_active' => false]);

        $this->toggle($hidden)->assertNotFound();
        $this->get('/en/wishlist')->assertDontSee('Black Abaya')->assertSee('No favourites yet.');
        // A saved product that became hidden can still be removed.
        $this->toggle($this->abaya)->assertJson(['saved' => false]);
    }

    public function test_empty_page_and_translations(): void
    {
        $this->get('/en/wishlist')->assertOk()->assertSee('No favourites yet.');
        $this->get('/ar/wishlist')->assertSee('المفضلة')->assertSee('لا توجد منتجات في المفضلة بعد.');
        $this->get('/nl/wishlist')->assertSee('Favorieten');
    }

    public function test_cookie_policy_lists_the_cookie(): void
    {
        $this->get('/en/cookies')->assertSee('wishlist')->assertSee('1 year, or until you log in');
    }
}
