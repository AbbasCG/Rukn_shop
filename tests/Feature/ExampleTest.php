<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_the_default_locale(): void
    {
        $this->get('/')->assertRedirect('/nl');
    }

    public function test_home_page_renders_in_every_locale(): void
    {
        foreach (['nl', 'en', 'ar'] as $locale) {
            $this->get("/{$locale}")->assertOk();
        }
    }
}
