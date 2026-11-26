<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Per-page SEO data. Everything is optional; the layout falls back to the
     * site defaults in lang/<locale>/seo.php.
     *
     * @param  string|null  $title  Page title without the site name ("Abayas").
     * @param  string|null  $description  Meta / social description.
     * @param  string|null  $image  Absolute URL of the social sharing image.
     * @param  string  $type  Open Graph type: "website" or "product".
     * @param  bool  $noindex  Keep the page out of search results (cart, account…).
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $image = null,
        public string $type = 'website',
        public bool $noindex = false,
    ) {
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
