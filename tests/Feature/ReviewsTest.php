<?php

namespace Tests\Feature;

use App\Mail\ReviewRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReviewsTest extends TestCase
{
    use RefreshDatabase;

    private Product $abaya;

    private Product $mabkhara;

    protected function setUp(): void
    {
        parent::setUp();
        config(['shop.contact_email' => 'info@rukn.nl', 'shop.reviews.request_after_days' => 7]);
        $category = Category::factory()->create();
        $this->abaya = Product::factory()->for($category)->create(['name' => 'Black Abaya', 'is_active' => true, 'stock' => 5]);
        $this->mabkhara = Product::factory()->for($category)->create(['name' => 'Gold Mabkhara', 'is_active' => true, 'stock' => 5]);
    }

    private function order(string $status = 'delivered', array $attributes = []): Order
    {
        $order = Order::create($attributes + [
            'status' => $status, 'payment_status' => 'open', 'payment_method' => 'cod', 'locale' => 'nl',
            'name' => 'Fatima El Amrani', 'email' => 'fatima@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam', 'country' => 'NL',
            'subtotal' => 88.95, 'shipping_cost' => 0, 'total' => 88.95,
            'delivered_at' => $status === 'delivered' ? now() : null,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->abaya->id, 'product_name' => 'Black Abaya', 'quantity' => 1, 'price' => 49]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->mabkhara->id, 'product_name' => 'Gold Mabkhara', 'quantity' => 1, 'price' => 39.95]);

        return $order;
    }

    private function review(Order $order, Product $product, array $data = [])
    {
        return $this->post(route('reviews.store', ['locale' => 'en', 'order' => $order->id, 'token' => $order->access_token]), $data + [
            'product_id' => $product->id, 'rating' => 5, 'comment' => 'Beautiful fabric, perfect length.', 'author_name' => 'Fatima',
        ]);
    }

    // ---- Writing a review ----

    public function test_a_buyer_can_review_a_delivered_order_from_the_private_link(): void
    {
        $order = $this->order();

        $this->get($order->reviewUrl('en'))->assertOk()
            ->assertSee('Black Abaya')->assertSee('Gold Mabkhara')
            ->assertSee('value="Fatima"', false);   // first name only, prefilled

        $this->review($order, $this->abaya)->assertRedirect($order->reviewUrl('en'))->assertSessionHas('status');

        $review = Review::sole();
        $this->assertSame('pending', $review->status);
        $this->assertSame($order->id, $review->order_id);
        $this->assertNull($review->user_id);          // guest order
        $this->assertTrue($review->isVerified());

        // The reviewed product disappears from the form; the review is listed as waiting.
        $this->get($order->reviewUrl('en'))->assertSee('Waiting for approval')
            ->assertDontSee('name="product_id" value="' . $this->abaya->id . '"', false)
            ->assertSee('name="product_id" value="' . $this->mabkhara->id . '"', false);
    }

    public function test_an_account_order_keeps_the_user_on_the_review(): void
    {
        $user = User::factory()->create();
        $order = $this->order('delivered', ['user_id' => $user->id]);

        $this->review($order, $this->abaya);

        $this->assertSame($user->id, Review::sole()->user_id);
    }

    public function test_one_review_per_product_per_order(): void
    {
        $order = $this->order();
        $this->review($order, $this->abaya);

        $this->review($order, $this->abaya)->assertSessionHasErrors('product_id');
        $this->assertSame(1, Review::count());
    }

    public function test_only_products_from_the_order_can_be_reviewed(): void
    {
        $other = Product::factory()->for(Category::factory())->create(['is_active' => true]);

        $this->review($this->order(), $other)->assertSessionHasErrors('product_id');
        $this->assertSame(0, Review::count());
    }

    public function test_not_before_delivery(): void
    {
        $order = $this->order('shipped');

        $this->get($order->reviewUrl('en'))->assertOk()->assertSee('once it has been delivered')->assertDontSee('name="rating"', false);
        $this->review($order, $this->abaya)->assertForbidden();
    }

    public function test_the_link_needs_the_order_token(): void
    {
        $order = $this->order();
        $wrong = str_repeat('x', 40);

        $this->get("/en/orders/review/{$order->id}/{$wrong}")->assertNotFound();
        $this->post("/en/orders/review/{$order->id}/{$wrong}", ['product_id' => $this->abaya->id, 'rating' => 5, 'author_name' => 'X'])->assertNotFound();
    }

    public function test_validation(): void
    {
        $order = $this->order();

        $this->review($order, $this->abaya, ['rating' => 6])->assertSessionHasErrors('rating');
        $this->review($order, $this->abaya, ['rating' => 0])->assertSessionHasErrors('rating');
        $this->review($order, $this->abaya, ['author_name' => ''])->assertSessionHasErrors('author_name');
        $this->review($order, $this->abaya, ['comment' => str_repeat('a', 2001)])->assertSessionHasErrors('comment');
        $this->assertSame(0, Review::count());

        $this->review($order, $this->abaya, ['comment' => null])->assertSessionHasNoErrors(); // stars only is fine
    }

    public function test_the_order_page_invites_to_review_once_delivered(): void
    {
        $delivered = $this->order();
        $this->get($delivered->privateUrl('en'))->assertSee('Write a review')->assertSee($delivered->reviewUrl('en'), false);

        $shipped = $this->order('shipped');
        $this->get($shipped->privateUrl('en'))->assertDontSee('Write a review');
    }

    // ---- Moderation ----

    public function test_reviews_show_in_the_shop_only_after_approval(): void
    {
        $order = $this->order();
        $this->review($order, $this->abaya, ['comment' => 'Lovely <b>abaya</b>']);
        $review = Review::sole();

        $this->get('/en/products/' . $this->abaya->slug)->assertDontSee('Lovely')->assertDontSee('Customer reviews');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/en/admin/reviews')->assertOk()->assertSee('Lovely &lt;b&gt;abaya&lt;/b&gt;', false);
        $this->actingAs($admin)->patch("/en/admin/reviews/{$review->id}", ['status' => 'approved'])->assertRedirect();
        $this->assertSame('approved', $review->fresh()->status);
        $this->assertNotNull($review->fresh()->approved_at);

        auth()->logout();
        $this->get('/en/products/' . $this->abaya->slug)->assertOk()
            ->assertSee('Customer reviews')
            ->assertSee('Lovely &lt;b&gt;abaya&lt;/b&gt;', false)   // escaped
            ->assertSee('Verified purchase')
            ->assertSee('Fatima')
            ->assertDontSee('fatima@example.com');
    }

    public function test_rejecting_and_deleting(): void
    {
        $order = $this->order();
        $this->review($order, $this->abaya);
        $review = Review::sole();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch("/en/admin/reviews/{$review->id}", ['status' => 'rejected']);
        $this->assertSame('rejected', $review->fresh()->status);
        $this->get('/en/products/' . $this->abaya->slug)->assertDontSee('Customer reviews');

        $this->actingAs($admin)->delete("/en/admin/reviews/{$review->id}")->assertRedirect();
        $this->assertSame(0, Review::count());
    }

    public function test_only_admins_moderate(): void
    {
        $order = $this->order();
        $this->review($order, $this->abaya);
        $review = Review::sole();

        $this->actingAs(User::factory()->create())->patch("/en/admin/reviews/{$review->id}", ['status' => 'approved'])->assertForbidden();
        $this->assertSame('pending', $review->fresh()->status);
    }

    public function test_admin_sidebar_counts_waiting_reviews(): void
    {
        $order = $this->order();
        $this->review($order, $this->abaya);
        $this->review($order, $this->mabkhara);

        $html = $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/en/admin')->getContent();
        $this->assertMatchesRegularExpression('#href="[^"]*/en/admin/reviews"[^>]*>.*?Reviews.*?>2</span>.*?</a>#s', $html);
    }

    // ---- Product page ----

    public function test_without_reviews_no_empty_stars_are_shown(): void
    {
        $html = $this->get('/en/products/' . $this->abaya->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString('(0 reviews)', $html);
        $this->assertStringNotContainsString('href="#reviews"', $html);
        $this->assertStringNotContainsString('aggregateRating', $html);
        $this->assertStringNotContainsString('Customer reviews', $html);
    }

    public function test_average_count_and_structured_data(): void
    {
        foreach ([5, 4] as $rating) {
            Review::create(['product_id' => $this->abaya->id, 'order_id' => $this->order()->id, 'author_name' => 'Sara', 'rating' => $rating, 'status' => 'approved', 'approved_at' => now()]);
        }
        Review::create(['product_id' => $this->abaya->id, 'order_id' => $this->order()->id, 'author_name' => 'Spam', 'rating' => 1, 'status' => 'pending']);

        $html = $this->get('/en/products/' . $this->abaya->slug)->assertOk()->getContent();

        $this->assertStringContainsString('4.5', $html);
        $this->assertStringContainsString('(2 reviews)', $html);
        $this->assertStringContainsString('"aggregateRating":{"@type":"AggregateRating","ratingValue":"4.5","reviewCount":2', $html);
        $this->assertStringNotContainsString('Spam', $html);

        $this->get('/ar/products/' . $this->abaya->slug)->assertSee('تقييمان')->assertSee('شراء مؤكَّد');
    }

    // ---- Request email ----

    public function test_delivered_date_is_set_once_when_the_admin_marks_delivered(): void
    {
        $order = $this->order('shipped');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch("/en/admin/orders/{$order->id}", ['status' => 'delivered']);
        $first = $order->fresh()->delivered_at;
        $this->assertNotNull($first);

        $this->travel(2)->days();
        $this->actingAs($admin)->patch("/en/admin/orders/{$order->id}", ['status' => 'delivered']);
        $this->assertTrue($first->equalTo($order->fresh()->delivered_at));
    }

    public function test_request_email_goes_out_7_days_after_delivery_once(): void
    {
        Mail::fake();
        $order = $this->order('delivered', ['delivered_at' => now()->subDays(6)]);

        $this->artisan('reviews:send-requests')->assertSuccessful();
        Mail::assertNothingOutgoing();

        $this->travel(1)->days();
        $this->artisan('reviews:send-requests');
        $this->artisan('reviews:send-requests');

        Mail::assertQueued(ReviewRequest::class, function (ReviewRequest $mail) use ($order) {
            $mail->assertSeeInHtml($order->reviewUrl('nl'))->assertSeeInHtml('Black Abaya')->assertSeeInText('Gold Mabkhara');

            return $mail->hasTo('fatima@example.com') && $mail->locale === 'nl'
                && $mail->hasSubject('Hoe bevalt je bestelling #' . $order->number . '?');
        });
        Mail::assertQueuedCount(1);
        $this->assertNotNull($order->fresh()->review_requested_at);
    }

    public function test_no_request_for_cancelled_old_or_already_reviewed_orders(): void
    {
        Mail::fake();
        $this->order('cancelled', ['delivered_at' => now()->subDays(8)]);
        $this->order('delivered', ['delivered_at' => now()->subDays(90)]);          // long before this feature
        $done = $this->order('delivered', ['delivered_at' => now()->subDays(8)]);
        Review::create(['product_id' => $this->abaya->id, 'order_id' => $done->id, 'author_name' => 'A', 'rating' => 5]);
        Review::create(['product_id' => $this->mabkhara->id, 'order_id' => $done->id, 'author_name' => 'A', 'rating' => 5]);

        $this->artisan('reviews:send-requests');

        Mail::assertNothingOutgoing();
    }

    public function test_the_arabic_request_email_is_right_to_left(): void
    {
        $order = $this->order('delivered', ['locale' => 'ar']);

        (new ReviewRequest($order))->assertSeeInHtml('dir="rtl"', false)->assertSeeInHtml('اكتب تقييماً');
    }

    public function test_the_scheduler_runs_the_queue_and_the_review_requests(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('queue:work')->expectsOutputToContain('reviews:send-requests');
    }

    public function test_privacy_policy_mentions_reviews(): void
    {
        $this->get('/en/privacy-policy')->assertSee('When you write a review');
    }
}
