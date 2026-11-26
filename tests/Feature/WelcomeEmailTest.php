<?php

namespace Tests\Feature;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'shop.contact_email' => 'info@rukn.nl',
            'shop.shipping.free_threshold' => 50,
            'shop.shipping.flat_rate' => 4.95,
            'shop.returns.days' => 14,
        ]);
    }

    private function register(string $locale, string $email = 'fatima@example.com')
    {
        return $this->post("/{$locale}/register", [
            'name' => 'Fatima', 'email' => $email, 'phone' => '0612345678',
            'password' => 'password', 'password_confirmation' => 'password',
        ]);
    }

    public function test_registering_sends_one_welcome_email_in_the_signup_language(): void
    {
        Mail::fake();

        $this->register('ar')->assertRedirect();

        Mail::assertQueued(WelcomeEmail::class, function (WelcomeEmail $mail) {
            return $mail->hasTo('fatima@example.com')
                && $mail->locale === 'ar'
                && $mail->hasReplyTo('info@rukn.nl');
        });
        Mail::assertQueuedCount(1);
    }

    public function test_arabic_welcome_email_is_right_to_left_and_links_to_arabic_pages(): void
    {
        $mail = new WelcomeEmail(User::factory()->create(['name' => 'Fatima']), 'ar');

        $mail->assertHasSubject('أهلاً بك في ركن يا Fatima');
        $mail->assertSeeInHtml('dir="rtl"', false)
            ->assertSeeInHtml('أهلاً وسهلاً، Fatima!')
            ->assertSeeInHtml(route('products.index', ['locale' => 'ar']))
            ->assertSeeInHtml(route('orders.index', ['locale' => 'ar']))
            ->assertDontSeeInHtml('ركن شوب');
    }

    public function test_welcome_email_states_the_real_shipping_and_return_policy(): void
    {
        $mail = new WelcomeEmail(User::factory()->create(['name' => 'Anna']), 'en');

        $mail->assertSeeInHtml('dir="ltr"', false)
            ->assertSeeInHtml('Free shipping from €50.00 (otherwise €4.95)')
            ->assertSeeInHtml('within 14 days')
            ->assertSeeInText('Welcome, Anna!')
            ->assertSeeInText(route('products.index', ['locale' => 'en']))
            ->assertDontSeeInText('&amp;');

        (new WelcomeEmail(User::factory()->create(['name' => 'Sanne']), 'nl'))->assertHasSubject('Welkom bij Rukn, Sanne');
    }

    public function test_a_mail_failure_does_not_block_registration(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));
        Log::shouldReceive('error')->once()->withArgs(fn ($message) => $message === 'Welcome email could not be sent.');

        $this->register('nl')->assertRedirect();

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'fatima@example.com']);
    }
}
