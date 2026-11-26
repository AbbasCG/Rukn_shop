<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordResetEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['shop.contact_email' => 'info@rukn.nl']);
    }

    private function requestLink(User $user, string $locale = 'en'): PasswordResetEmail
    {
        $this->post("/{$locale}/forgot-password", ['email' => $user->email]);

        $sent = null;
        Mail::assertSent(PasswordResetEmail::class, function (PasswordResetEmail $mail) use (&$sent) {
            $sent = $mail;

            return true;
        });

        return $sent;
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->get('/en/forgot-password')->assertOk()
            ->assertSee('href="' . route('orders.lookup', ['locale' => 'en']) . '"', false);
    }

    public function test_the_reset_email_is_branded_and_in_the_page_language(): void
    {
        $user = User::factory()->create(['name' => 'Fatima']);

        $mail = $this->requestLink($user, 'ar');

        $this->assertTrue($mail->hasTo($user->email));
        $this->assertSame('ar', $mail->locale);
        $this->assertTrue($mail->hasReplyTo('info@rukn.nl'));
        $mail->assertHasSubject('إعادة تعيين كلمة السر لحسابك في ركن');
        $mail->assertSeeInHtml('dir="rtl"', false)
            ->assertSeeInHtml('اختر كلمة سر جديدة')
            ->assertSeeInHtml('هذا الرابط صالح لمدة 60 دقيقة')
            ->assertSeeInHtml(route('password.reset', ['locale' => 'ar', 'token' => $mail->token, 'email' => $user->email]))
            ->assertSeeInText('/ar/reset-password/' . $mail->token);
    }

    public function test_the_answer_does_not_reveal_whether_an_account_exists(): void
    {
        $user = User::factory()->create();
        $message = 'If an account exists for this email address, we have sent a link to reset the password. Please also check your spam folder.';

        $this->post('/en/forgot-password', ['email' => $user->email])->assertSessionHas('status', $message)->assertSessionHasNoErrors();
        $this->post('/en/forgot-password', ['email' => 'nobody@example.com'])->assertSessionHas('status', $message)->assertSessionHasNoErrors();
        $this->post('/en/forgot-password', ['email' => $user->email])->assertSessionHas('status', $message)->assertSessionHasNoErrors(); // broker-throttled

        Mail::assertSent(PasswordResetEmail::class, 1);
    }

    public function test_email_case_does_not_matter(): void
    {
        $user = User::factory()->create(['email' => 'fatima@example.com']);

        $this->post('/en/forgot-password', ['email' => ' Fatima@Example.com ']);

        Mail::assertSent(PasswordResetEmail::class, fn ($mail) => $mail->hasTo('fatima@example.com'));
    }

    public function test_requests_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/en/forgot-password', ['email' => "x{$i}@example.com"])->assertRedirect();
        }

        $this->post('/en/forgot-password', ['email' => 'x9@example.com'])->assertStatus(429);
    }

    public function test_a_mail_failure_is_logged_and_the_same_answer_is_given(): void
    {
        $user = User::factory()->create();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));
        Log::shouldReceive('error')->once()->withArgs(fn ($message) => $message === 'Password reset email could not be sent.');

        $this->post('/en/forgot-password', ['email' => $user->email])->assertSessionHas('status')->assertSessionHasNoErrors();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $mail = $this->requestLink(User::factory()->create());

        $this->get('/en/reset-password/' . $mail->token)->assertOk();
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $mail = $this->requestLink($user);

        $this->post('/en/reset-password', [
            'token' => $mail->token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $this->post('/en/login', ['email' => $user->email, 'password' => 'new-password']);
        $this->assertAuthenticated();
    }

    public function test_an_invalid_link_says_to_request_a_new_one(): void
    {
        $user = User::factory()->create();

        $this->post('/en/reset-password', [
            'token' => 'wrong', 'email' => $user->email,
            'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors(['email' => 'This reset link is invalid or has expired. Please request a new one.']);
    }
}
