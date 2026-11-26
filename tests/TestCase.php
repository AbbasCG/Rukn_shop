<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    /**
     * Locale used for the {locale} prefix that every public route has.
     */
    protected string $locale = 'en';

    protected function setUp(): void
    {
        parent::setUp();

        // Lets route('login') etc. be generated in tests without passing a
        // locale each time, the same way the SetLocale middleware does.
        URL::defaults(['locale' => $this->locale]);
    }
}
