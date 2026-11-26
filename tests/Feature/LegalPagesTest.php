<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    // ---- Cookie policy ----

    public function test_cookie_page_lists_the_cookies_the_shop_really_sets(): void
    {
        config(['session.cookie' => 'rukn-session', 'session.lifetime' => 120]);

        $this->get('/en/cookies')->assertOk()
            ->assertSee('rukn-session')
            ->assertSee('120 minutes after your last visit')
            ->assertSee('XSRF-TOKEN')
            ->assertSee('remember_web_')
            ->assertSee('no analytics, advertising or tracking cookies');
    }

    public function test_cookie_page_mentions_mollie_only_when_online_payment_is_on(): void
    {
        config(['services.mollie.key' => null]);
        $this->get('/en/cookies')->assertDontSee('Mollie');

        config(['services.mollie.key' => 'test_x']);
        $this->get('/en/cookies')->assertSee('Mollie');
    }

    public function test_cookie_page_is_translated(): void
    {
        $this->get('/ar/cookies')->assertOk()->assertSee('سياسة ملفات تعريف الارتباط');
        $this->get('/nl/cookies')->assertOk()->assertSee('Cookiebeleid');
    }

    // ---- Withdrawal form ----

    public function test_withdrawal_form_uses_the_official_dutch_and_english_wording(): void
    {
        $this->get('/nl/withdrawal-form')->assertOk()
            ->assertSee('(dit formulier alleen invullen en terugzenden wanneer u de overeenkomst wilt herroepen)')
            ->assertSee('Handtekening consument(en) (alleen wanneer dit formulier op papier wordt ingediend)');

        $this->get('/en/withdrawal-form')->assertOk()
            ->assertSee('(complete and return this form only if you wish to withdraw from the contract)')
            ->assertSee('I/We (*) hereby give notice that I/We (*) withdraw from my/our (*) contract of sale');
    }

    public function test_withdrawal_form_shows_the_business_details(): void
    {
        config(['shop.business' => ['name' => 'Rukn', 'address' => 'Damrak 1, 1012 LG Amsterdam', 'kvk' => '12345678'],
                'shop.contact_email' => 'info@rukn.nl']);

        $this->get('/en/withdrawal-form')->assertOk()
            ->assertSee('Damrak 1, 1012 LG Amsterdam')
            ->assertSee('12345678')
            ->assertSee('mailto:info@rukn.nl', false);
    }

    public function test_withdrawal_form_without_an_address_says_how_to_get_it(): void
    {
        config(['shop.business' => ['name' => 'Rukn', 'address' => null, 'kvk' => null], 'shop.contact_email' => 'info@rukn.nl']);

        $this->get('/en/withdrawal-form')->assertOk()
            ->assertSee('Our postal address is available on request at info@rukn.nl.')
            ->assertDontSee('Chamber of Commerce');
    }

    public function test_arabic_withdrawal_form_is_marked_as_a_translation(): void
    {
        $this->get('/ar/withdrawal-form')->assertOk()
            ->assertSee('نموذج التراجع عن الشراء')
            ->assertSee('هذه ترجمة عربية للنموذج الرسمي');
    }

    // ---- Where they are linked ----

    public function test_footer_links_to_cookies_and_the_withdrawal_form(): void
    {
        $this->get('/en')->assertOk()
            ->assertSee('href="' . route('cookies', ['locale' => 'en']) . '"', false)
            ->assertSee('href="' . route('withdrawal-form', ['locale' => 'en']) . '"', false);
    }

    public function test_checkout_informs_about_terms_and_withdrawal_before_ordering(): void
    {
        $product = Product::factory()->for(Category::factory())->create(['stock' => 3, 'is_active' => true]);
        $this->postJson('/en/cart', ['product_id' => $product->id]);

        $this->get('/en/checkout')->assertOk()
            ->assertSee('By placing your order you agree to our')
            ->assertSee('href="' . route('withdrawal-form', ['locale' => 'en']) . '"', false)
            ->assertSee('href="' . route('terms-of-service', ['locale' => 'en']) . '"', false);
    }

    public function test_terms_link_to_the_withdrawal_form(): void
    {
        $this->get('/en/terms-of-service')->assertOk()
            ->assertSee('href="' . route('withdrawal-form', ['locale' => 'en']) . '"', false);
    }

    public function test_legal_pages_show_a_fixed_localised_update_date(): void
    {
        $this->get('/nl/terms-of-service')->assertSee('26 november 2026');
        $this->get('/nl/privacy-policy')->assertSee('26 november 2026');
        $this->get('/en/privacy-policy')->assertDontSee(date('F j, Y')); // not "today" on every visit
    }

    public function test_sitemap_includes_the_new_pages(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertSee(route('cookies', ['locale' => 'ar']))
            ->assertSee(route('withdrawal-form', ['locale' => 'nl']));
    }

    // ---- Privacy policy ----

    public function test_privacy_policy_names_the_business_and_the_real_contact_address(): void
    {
        config(['shop.business' => ['name' => 'Rukn', 'address' => 'Damrak 1, 1012 LG Amsterdam', 'kvk' => '12345678'],
                'shop.contact_email' => 'info@rukn.nl']);

        $this->get('/en/privacy-policy')->assertOk()
            ->assertSee('Rukn is responsible')
            ->assertSee('Damrak 1, 1012 LG Amsterdam')
            ->assertSee('12345678')
            ->assertSee('mailto:info@rukn.nl', false)
            ->assertSee('email us at info@rukn.nl')
            ->assertDontSee('privacy@ruknshop.com');
    }

    public function test_privacy_policy_makes_no_false_claims(): void
    {
        config(['services.mollie.key' => 'test_x']);

        $this->get('/en/privacy-policy')->assertOk()
            ->assertDontSee('analyze site traffic')
            ->assertDontSee('marketing communications')
            ->assertSee('do not use analytics or tracking')
            ->assertSee('We never see or store your card or bank details')
            ->assertSee('7 years')
            ->assertSee('Autoriteit Persoonsgegevens')
            ->assertDontSee('Bunny')
            ->assertSee('href="' . route('cookies', ['locale' => 'en']) . '"', false);
    }

    public function test_privacy_policy_lists_mollie_and_cloudflare_only_when_used(): void
    {
        config(['services.mollie.key' => null, 'shop.media_disk' => 'public']);
        $this->get('/en/privacy-policy')->assertDontSee('Mollie')->assertDontSee('Cloudflare')
            ->assertSee('You pay on delivery.');

        config(['services.mollie.key' => 'test_x', 'shop.media_disk' => 'r2']);
        $this->get('/en/privacy-policy')->assertSee('Mollie')->assertSee('Cloudflare (R2)');
    }

    public function test_privacy_policy_translations(): void
    {
        $this->get('/nl/privacy-policy')->assertOk()
            ->assertSee('Welke gegevens we verzamelen')
            ->assertDontSee('Dutch version applies');
        $this->get('/ar/privacy-policy')->assertOk()
            ->assertSee('البيانات التي نجمعها')
            ->assertSee('فالنص الهولندي هو المعتمد');
    }
}
