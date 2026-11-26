<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        $category = Category::factory()->create([
            'name' => 'Abayas', 'slug' => 'abayas', 'is_active' => true,
            'name_translations' => ['ar' => 'عبايات', 'nl' => "Abaya's"],
        ]);

        return Product::factory()->for($category)->create(array_merge([
            'name' => 'Black Abaya', 'price' => 49.5, 'stock' => 4, 'is_active' => true,
            'short_description' => 'A soft crepe abaya for every day.',
        ], $attributes));
    }

    /** Decode every JSON-LD block on the page. */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_merge(...array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]));
    }

    public function test_home_has_title_description_hreflang_and_social_tags(): void
    {
        $this->get('/ar')->assertOk()
            ->assertSee('<title>ركن – عبايات ومباخر كهربائية</title>', false)
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical" href="' . url('/ar') . '">', false)
            ->assertSee('hreflang="nl" href="' . url('/nl') . '"', false)
            ->assertSee('hreflang="en" href="' . url('/en') . '"', false)
            ->assertSee('hreflang="ar" href="' . url('/ar') . '"', false)
            ->assertSee('hreflang="x-default" href="' . url('/nl') . '"', false)
            ->assertSee('<meta property="og:locale" content="ar_AR">', false)
            ->assertSee('<meta property="og:image" content="' . asset('images/banner.jpg') . '">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false);
    }

    public function test_description_follows_the_shipping_policy(): void
    {
        config(['shop.shipping.free_threshold' => 75]);

        $this->get('/en')->assertSee('Free shipping from €75.00');
    }

    public function test_product_page_has_its_own_title_image_and_structured_data(): void
    {
        $product = $this->product();

        $response = $this->get("/en/products/{$product->id}")->assertOk()
            ->assertSee('<title>Black Abaya – Rukn</title>', false)
            ->assertSee('content="A soft crepe abaya for every day."', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('<meta property="product:price:amount" content="49.50">', false);

        $data = collect($this->jsonLd($response->getContent()))->keyBy('@type');

        $this->assertSame('Black Abaya', $data['Product']['name']);
        $this->assertSame('49.50', $data['Product']['offers']['price']);
        $this->assertSame('EUR', $data['Product']['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $data['Product']['offers']['availability']);
        $this->assertStringStartsWith('http', $data['Product']['image'][0]);
        $this->assertCount(4, $data['BreadcrumbList']['itemListElement']);
    }

    public function test_sold_out_products_are_marked_out_of_stock(): void
    {
        $product = $this->product(['stock' => 0]);

        $data = collect($this->jsonLd($this->get("/en/products/{$product->id}")->getContent()))->keyBy('@type');

        $this->assertSame('https://schema.org/OutOfStock', $data['Product']['offers']['availability']);
    }

    public function test_category_listing_keeps_the_category_in_the_canonical_but_not_the_sorting(): void
    {
        $this->product();

        $this->get('/ar/products?category=abayas&sort=price_low')->assertOk()
            ->assertSee('<title>عبايات – ركن</title>', false)
            ->assertSee('<link rel="canonical" href="' . url('/ar/products') . '?category=abayas">', false);
    }

    public function test_search_results_are_not_indexed(): void
    {
        $this->get('/en/products?q=abaya')->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_private_pages_are_not_indexed(): void
    {
        $this->get('/nl/cart')->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertDontSee('hreflang=', false);

        $this->get('/ar/login')->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<title>الحساب – ركن</title>', false);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/en/admin')->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_sitemap_lists_public_pages_and_active_products_in_every_language(): void
    {
        $active = $this->product();
        $hidden = Product::factory()->for($active->category)->create(['is_active' => false]);

        $response = $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'sitemap.xml is valid XML');

        // collect($xml->url) would only see the first <url>; iterate explicitly.
        $locs = collect();
        foreach ($xml->url as $url) {
            $locs->push((string) $url->loc);
        }
        $this->assertGreaterThan(20, $locs->count());
        foreach (['nl', 'en', 'ar'] as $locale) {
            $this->assertTrue($locs->contains(url("/{$locale}/products/{$active->id}")));
            $this->assertTrue($locs->contains(url("/{$locale}/faq")));
            $this->assertFalse($locs->contains(url("/{$locale}/products/{$hidden->id}")));
        }
        $this->assertFalse($locs->contains(fn ($loc) => str_contains($loc, '/cart') || str_contains($loc, '/admin')));
    }

    public function test_robots_txt_points_to_the_sitemap_and_blocks_private_areas(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: ' . url('/sitemap.xml'))
            ->assertSee('Disallow: /*/admin')
            ->assertSee('Disallow: /*/checkout')
            ->assertDontSee('Disallow: /*/products');
    }

    public function test_urls_use_https_behind_the_hosting_proxy(): void
    {
        $this->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'rukn.nl', 'X-Forwarded-Port' => '443'])
            ->get('http://localhost/nl')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://rukn.nl/nl">', false)
            ->assertSee('hreflang="ar" href="https://rukn.nl/ar"', false);
    }
}
