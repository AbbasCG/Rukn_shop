<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'shop.contact_email' => 'info@rukn.nl',
            'shop.order_notifications.locale' => 'ar',
        ]);
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Fatima',
            'email' => 'fatima@example.com',
            'subject' => 'Size question',
            'message' => 'Do you have this abaya in size 58?',
        ], $overrides);
    }

    public function test_contact_page_has_a_working_form(): void
    {
        $this->get('/nl/contact')
            ->assertOk()
            ->assertSee('action="' . route('contact.store', ['locale' => 'nl']) . '"', false)
            ->assertSee('name="message"', false)
            ->assertDontSee('onsubmit="return false;"', false);
    }

    public function test_a_valid_message_is_stored_and_emailed_to_the_store(): void
    {
        Mail::fake();

        $this->from('/ar/contact')->post('/ar/contact', $this->validData())
            ->assertRedirect('/ar/contact')
            ->assertSessionHas('success', 'شكراً! تم إرسال رسالتك، وسنرد عليك بالإيميل.');

        $message = ContactMessage::sole();
        $this->assertSame('ar', $message->locale);
        $this->assertNull($message->read_at);
        $this->assertNull($message->user_id);

        Mail::assertQueued(ContactMessageReceived::class, function (ContactMessageReceived $mail) use ($message) {
            return $mail->hasTo('info@rukn.nl')
                && $mail->hasReplyTo('fatima@example.com')
                && $mail->locale === 'ar'
                && $mail->contactMessage->is($message);
        });
    }

    public function test_logged_in_customer_is_linked_to_the_message(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/en/contact', $this->validData());

        $this->assertSame($user->id, ContactMessage::sole()->user_id);
    }

    public function test_email_content(): void
    {
        $message = ContactMessage::create($this->validData(['locale' => 'nl']));
        $mail = new ContactMessageReceived($message);

        $mail->assertHasSubject('رسالة من Fatima: Size question');
        $mail->assertSeeInHtml('Do you have this abaya in size 58?');
        $mail->assertSeeInHtml('fatima@example.com');
        $mail->assertSeeInHtml('Nederlands');
        $mail->assertSeeInHtml('/ar/admin/messages');
        $mail->assertSeeInText('Do you have this abaya in size 58?');
    }

    public function test_invalid_input_is_rejected_with_translated_errors_and_nothing_is_stored(): void
    {
        Mail::fake();

        $this->from('/ar/contact')->post('/ar/contact', ['name' => '', 'email' => 'not-an-email', 'message' => 'short'])
            ->assertRedirect('/ar/contact')
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertSame('حقل الاسم مطلوب.', session('errors')->first('name'));
        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingOutgoing();
    }

    public function test_honeypot_submissions_are_silently_dropped(): void
    {
        Mail::fake();

        $this->from('/en/contact')->post('/en/contact', $this->validData(['website' => 'http://spam.example']))
            ->assertRedirect('/en/contact')
            ->assertSessionHas('success');

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingOutgoing();
    }

    public function test_submissions_are_rate_limited(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/en/contact', $this->validData())->assertRedirect();
        }

        $this->post('/en/contact', $this->validData())->assertStatus(429);
        $this->assertDatabaseCount('contact_messages', 5);
    }

    public function test_message_is_kept_when_the_email_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->post('/en/contact', $this->validData())->assertSessionHas('success');

        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_admin_can_read_filter_mark_and_delete_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $unread = ContactMessage::create($this->validData(['subject' => 'Unread one', 'locale' => 'ar']));
        $read = ContactMessage::create($this->validData(['subject' => 'Read one', 'read_at' => now()]));

        $this->actingAs($admin)->get('/en/admin/messages')
            ->assertOk()->assertSee('Unread one')->assertSee('Read one');

        $this->actingAs($admin)->get('/en/admin/messages?filter=unread')
            ->assertOk()->assertSee('Unread one')->assertDontSee('Read one');

        // Sidebar shows the unread count on every admin page.
        $this->actingAs($admin)->get('/en/admin')->assertSee('admin/messages', false);

        $this->actingAs($admin)->patch("/en/admin/messages/{$unread->id}", ['read' => 1])->assertRedirect();
        $this->assertNotNull($unread->fresh()->read_at);

        $this->actingAs($admin)->patch("/en/admin/messages/{$unread->id}", ['read' => 0]);
        $this->assertNull($unread->fresh()->read_at);

        $this->actingAs($admin)->delete("/en/admin/messages/{$read->id}")->assertRedirect();
        $this->assertModelMissing($read);
    }

    public function test_customers_cannot_access_admin_messages(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $message = ContactMessage::create($this->validData());

        $this->actingAs($customer)->get('/en/admin/messages')->assertForbidden();
        $this->actingAs($customer)->delete("/en/admin/messages/{$message->id}")->assertForbidden();
        $this->assertModelExists($message);
    }
}
