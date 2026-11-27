<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use App\Mail\WelcomeEmail;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BackgroundEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_shop_email_is_sent_in_the_background(): void
    {
        foreach (glob(app_path('Mail/*.php')) as $file) {
            $class = 'App\\Mail\\' . basename($file, '.php');
            if ($class === \App\Mail\TwoFactorCode::class) {
                // The only exception: the admin waits on the code page, and
                // the queue runs once a minute (see TwoFactorTest).
                $this->assertFalse(is_subclass_of($class, ShouldQueue::class));
                continue;
            }
            $this->assertTrue(is_subclass_of($class, ShouldQueue::class), "{$class} should implement ShouldQueue");
            $this->assertSame(4, (new \ReflectionClass($class))->getDefaultProperties()['tries'], $class);
        }
    }

    public function test_checkout_puts_the_emails_on_the_database_queue_instead_of_sending_them(): void
    {
        config(['queue.default' => 'database', 'shop.order_notifications.emails' => ['shop@rukn.nl']]);
        $product = Product::factory()->for(Category::factory())->create(['stock' => 3, 'is_active' => true]);

        $this->postJson('/en/cart', ['product_id' => $product->id]);
        $this->post('/en/checkout', [
            'name' => 'Fatima', 'email' => 'f@example.com', 'phone' => '0612345678',
            'address_line1' => 'Teststraat 1', 'postal_code' => '1234AB', 'city' => 'Amsterdam',
            'country' => 'NL', 'shipping_method' => 'flat', 'payment_method' => 'cod',
        ])->assertRedirect();

        $this->assertSame(2, DB::table('jobs')->count());   // customer + staff
        $this->assertStringContainsString('OrderConfirmation', DB::table('jobs')->first()->payload);
    }

    public function test_the_worker_sends_a_queued_email_in_the_right_language(): void
    {
        config(['queue.default' => 'database']);
        Mail::to('f@example.com')->send(new WelcomeEmail(User::factory()->create(['name' => 'Fatima']), 'ar'));
        $this->assertSame(1, DB::table('jobs')->count());

        $sent = [];
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSent::class, function ($event) use (&$sent) {
            $sent[] = $event->message->getSubject();
        });
        $this->artisan('queue:work', ['--once' => true, '--stop-when-empty' => true])->assertSuccessful();

        $this->assertSame(['أهلاً بك في ركن يا Fatima'], $sent);
        $this->assertSame(0, DB::table('jobs')->count());
    }

    public function test_dashboard_warns_about_failed_emails_and_can_retry_or_discard_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'connection' => 'database', 'queue' => 'default',
            'payload' => json_encode(['displayName' => OrderConfirmation::class, 'job' => 'x', 'data' => []]),
            'exception' => 'Connection to smtp failed', 'failed_at' => now(),
        ]);

        $this->actingAs($admin)->get('/en/admin')->assertOk()
            ->assertSee('1 email(s) could not be sent')
            ->assertSee('Send again');

        $this->actingAs($admin)->post('/en/admin/mail-queue/discard')->assertRedirect();
        $this->assertSame(0, DB::table('failed_jobs')->count());
        $this->actingAs($admin)->get('/en/admin')->assertDontSee('could not be sent');
    }

    public function test_dashboard_warns_when_emails_are_stuck(): void
    {
        config(['queue.default' => 'database']);
        $admin = User::factory()->create(['role' => 'admin']);
        DB::table('jobs')->insert([
            'queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'reserved_at' => null,
            'available_at' => now()->subMinutes(30)->getTimestamp(), 'created_at' => now()->subMinutes(30)->getTimestamp(),
        ]);

        $this->actingAs($admin)->get('/en/admin')->assertSee('1 email(s) have been waiting for more than 15 minutes');
    }

    public function test_only_admins_can_retry_or_discard(): void
    {
        $this->actingAs(User::factory()->create())->post('/en/admin/mail-queue/retry')->assertForbidden();
        auth()->logout();
        $this->post('/en/admin/mail-queue/discard')->assertRedirect();   // guests go to login
    }
}
