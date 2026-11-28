<?php

return [
    'channel_description' => 'Products of :shop',
    'admin' => [
        'title' => 'Product feeds',
        'subtitle' => 'Links with all your products for Google Shopping and the Instagram / Facebook shop. The platforms fetch them themselves, so prices, sales and stock stay up to date.',
        'links' => 'Feed links (one per language)',
        'copy' => 'Copy',
        'open' => 'Open',
        'count' => ':count products are in the feed (products in sizes/colours appear once per size/colour).',
        'without_photo' => ':count active products have no photo and are left out (both platforms require one):',
        'google_steps' => [
            'Go to merchants.google.com and create a free account for your shop (country: Netherlands, currency: EUR).',
            'Verify and claim your website (rukn.nl) under Settings → Business info.',
            'Products → Add products → Add products from a file → "Add a file from a link" (scheduled fetch).',
            'Paste the Dutch feed link, choose daily.',
            'Under Shipping, check the delivery time; the feed already sends the shipping costs to the Netherlands.',
            'After the first fetch, check "Needs attention" under Products for any rejected items.',
        ],
        'meta_steps' => [
            'Go to business.facebook.com → Commerce Manager and create a catalogue (type: e-commerce).',
            'Catalogue → Data sources → Add items → Data feed → "Use a URL".',
            'Paste a feed link (Dutch, or the language your followers read), choose currency EUR and an update every day.',
            'Connect the catalogue to your Instagram account under Commerce Manager → Settings, then turn on product tags in Instagram.',
            'Meta reviews the shop first; after approval you can tag products in posts and stories.',
        ],
        'notes' => 'Prices include VAT. Sales are sent with their dates. Hidden products are never included. No gender or age group is sent: add them in Merchant Center if Google asks for them for clothing.',
    ],
];
