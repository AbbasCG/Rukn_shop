<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Mail\TwoFactorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'owner@example.com', 'password' => 'secret-pass']);
    }

    private function enable(User $user): void
    {
        $user->forceFill(['two_factor_enabled_at' => now()])->save();
    }

    private function login(string $password = 'secret-pass', bool $remember = false)
    {
        return $this->post('/en/login', ['email' => 'owner@example.com', 'password' => $password, 'remember' => $remember ? 'on' : null]);
    }

    private function sentCode(): string
    {
        $code = null;
        Mail::assertSent(TwoFactorCode::class, function (TwoFactorCode $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return $code;
    }

    // ---- Login ----

    public function test_without_two_factor_admins_log_in_as_before(): void
    {
        $this->login()->assertRedirect(route('admin.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($this->admin);
        Mail::assertNothingSent();
    }

    public function test_with_two_factor_the_password_alone_is_not_enough(): void
    {
        $this->enable($this->admin);

        $this->login()->assertRedirect(route('two-factor.challenge', ['locale' => 'en']));
        $this->assertGuest();

        Mail::assertSent(TwoFactorCode::class, function (TwoFactorCode $mail) {
            $this->assertMatchesRegularExpression('/^\d{6}$/', $mail->code);
            $mail->assertSeeInHtml($mail->code)->assertSeeInText($mail->code);

            return $mail->hasTo('owner@example.com');
        });
        // Not left waiting for the once-a-minute queue.
        Mail::assertNothingQueued();

        $this->get('/en/two-factor-challenge')->assertOk()->assertSee('Check your email')->assertSee('ow***@example.com');
        $this->get('/en/admin')->assertRedirect();
        $this->assertGuest();
    }

    public function test_the_right_code_logs_in_and_works_once(): void
    {
        $this->enable($this->admin);
        $this->login();
        $code = $this->sentCode();

        $this->post('/en/two-factor-challenge', ['code' => substr($code, 0, 3) . ' ' . substr($code, 3)])
            ->assertRedirect(route('admin.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($this->admin);

        // The same code cannot be replayed in a new login.
        $this->post('/en/logout');
        $this->login('secret-pass');
        Mail::assertSent(TwoFactorCode::class, 2);
    }

    public function test_remember_me_is_kept_until_the_code_is_entered(): void
    {
        $this->enable($this->admin);
        $this->login(remember: true);

        $response = $this->post('/en/two-factor-challenge', ['code' => $this->sentCode()]);

        $this->assertNotNull($this->admin->fresh()->remember_token);
        $response->assertCookie(auth()->guard('web')->getRecallerName());
    }

    public function test_wrong_codes_are_limited(): void
    {
        $this->enable($this->admin);
        $this->login();
        $code = $this->sentCode();
        $wrong = $code === '111111' ? '222222' : '111111';

        $this->from('/en/two-factor-challenge')->post('/en/two-factor-challenge', ['code' => $wrong])
            ->assertSessionHasErrors(['code' => 'This code is not correct.']);
        for ($i = 0; $i < 4; $i++) {
            $this->post('/en/two-factor-challenge', ['code' => $wrong]);
        }

        // After 5 wrong tries even the right code is refused: a new one is needed.
        $this->post('/en/two-factor-challenge', ['code' => $code])->assertSessionHasErrors(['code' => 'Too many wrong codes. Send a new code.']);
        $this->assertGuest();
    }

    public function test_codes_expire(): void
    {
        $this->enable($this->admin);
        $this->login();
        $code = $this->sentCode();

        $this->travel(11)->minutes();

        $this->post('/en/two-factor-challenge', ['code' => $code])->assertSessionHasErrors(['code' => 'This code has expired. Send a new code.']);
        $this->assertGuest();
    }

    public function test_resend_waits_a_minute_and_replaces_the_code(): void
    {
        $this->enable($this->admin);
        $this->login();
        $first = $this->sentCode();

        $this->from('/en/two-factor-challenge')->post('/en/two-factor-challenge/resend')->assertSessionHasErrors('code');
        Mail::assertSent(TwoFactorCode::class, 1);

        $this->travel(61)->seconds();
        $this->from('/en/two-factor-challenge')->post('/en/two-factor-challenge/resend')->assertSessionHas('status', 'A new code has been sent.');
        Mail::assertSent(TwoFactorCode::class, 2);

        $codes = Mail::sent(TwoFactorCode::class)->map->code;
        $second = $codes->last();
        if ($second !== $first) {
            $this->post('/en/two-factor-challenge', ['code' => $first])->assertSessionHasErrors('code');
        }
        $this->post('/en/two-factor-challenge', ['code' => $second])->assertRedirect(route('admin.dashboard', ['locale' => 'en']));
    }

    public function test_wrong_password_sends_no_code(): void
    {
        $this->enable($this->admin);

        $this->login('wrong')->assertSessionHasErrors('email');
        Mail::assertNothingSent();
        $this->get('/en/two-factor-challenge')->assertRedirect(route('login', ['locale' => 'en']));
    }

    public function test_customers_are_never_asked_for_a_code(): void
    {
        $customer = User::factory()->create(['email' => 'c@example.com', 'password' => 'secret-pass']);
        $customer->forceFill(['two_factor_enabled_at' => now()])->save();   // ignored for customers

        $this->post('/en/login', ['email' => 'c@example.com', 'password' => 'secret-pass'])->assertRedirect(route('home', ['locale' => 'en']));
        $this->assertAuthenticatedAs($customer);
        Mail::assertNothingSent();
    }

    public function test_a_mail_failure_does_not_log_in(): void
    {
        $this->enable($this->admin);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->from('/en/login')->login()->assertRedirect('/en/login')
            ->assertSessionHasErrors(['email' => 'The code could not be emailed. Please try again in a moment.']);
        $this->assertGuest();
    }

    // ---- Settings ----

    public function test_admin_turns_it_on_after_confirming_a_code(): void
    {
        $this->actingAs($this->admin);

        $this->get('/en/profile')->assertSee('Two-step verification')->assertSee('Turn on');

        $this->post('/en/profile/two-factor')->assertRedirect(route('profile.edit', ['locale' => 'en']) . '#two-factor');
        $this->assertNull($this->admin->fresh()->two_factor_enabled_at);   // not yet
        $this->get('/en/profile')->assertSee('Confirm and turn on');

        $this->post('/en/profile/two-factor/confirm', ['code' => '000000x'])->assertSessionHasErrors('code', null, 'twoFactor');
        $this->post('/en/profile/two-factor/confirm', ['code' => $this->sentCode()]);

        $this->assertNotNull($this->admin->fresh()->two_factor_enabled_at);
        $this->get('/en/profile')->assertSee('On since');
    }

    public function test_turning_it_off_needs_the_password(): void
    {
        $this->enable($this->admin);
        $this->actingAs($this->admin);

        $this->delete('/en/profile/two-factor', ['password' => 'nope'])->assertSessionHasErrors('password', null, 'twoFactor');
        $this->assertNotNull($this->admin->fresh()->two_factor_enabled_at);

        $this->delete('/en/profile/two-factor', ['password' => 'secret-pass']);
        $this->assertNull($this->admin->fresh()->two_factor_enabled_at);
    }

    public function test_customers_do_not_see_or_use_the_setting(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer);

        $this->get('/en/profile')->assertDontSee('Two-step verification');
        $this->post('/en/profile/two-factor')->assertForbidden();
        Mail::assertNothingSent();
    }

    public function test_emergency_command(): void
    {
        $this->enable($this->admin);

        $this->artisan('two-factor:disable', ['email' => 'owner@example.com'])->assertSuccessful();
        $this->assertNull($this->admin->fresh()->two_factor_enabled_at);
        $this->artisan('two-factor:disable', ['email' => 'nobody@example.com'])->assertFailed();
    }

    public function test_translated(): void
    {
        $this->enable($this->admin);
        $this->post('/ar/login', ['email' => 'owner@example.com', 'password' => 'secret-pass']);

        $this->get('/ar/two-factor-challenge')->assertSee('تحقق من بريدك الإلكتروني');
        Mail::assertSent(TwoFactorCode::class, fn (TwoFactorCode $mail) => $mail->locale === 'ar');
        $this->get('/nl/two-factor-challenge')->assertSee('Kijk in je e-mail');
    }

    public function test_pages_that_need_a_login_send_guests_to_the_login_page(): void
    {
        $this->get('/ar/admin')->assertRedirect(route('login', ['locale' => 'ar']));
        $this->get('/nl/profile')->assertRedirect(route('login', ['locale' => 'nl']));
        $this->get('/en/orders')->assertRedirect(route('login', ['locale' => 'en']));
    }

    public function test_email_is_masked(): void
    {
        $this->assertSame('ow***@example.com', TwoFactorChallengeController::maskEmail('owner@example.com'));
        $this->assertSame('a*@x.nl', TwoFactorChallengeController::maskEmail('ab@x.nl'));
    }
}
