<?php

/*
| Store policies. The storefront texts (product page, FAQ, terms, cart)
| read these values, so changing them here keeps prices and promises in
| sync. Each value can be overridden from the environment on Render.
*/

return [
    // The legal identity of the shop, shown on the withdrawal form and legal
    // pages. Dutch law expects the business address (and KvK number) to be
    // shown to customers: fill these in on Render.
    'business' => [
        'name' => env('SHOP_BUSINESS_NAME', 'Rukn'),
        'address' => env('SHOP_BUSINESS_ADDRESS'),     // e.g. "Damrak 1, 1012 LG Amsterdam, Nederland"
        'kvk' => env('SHOP_BUSINESS_KVK'),             // Chamber of Commerce number
    ],

    // Shown in emails and used as Reply-To, so customers can simply reply.
    'contact_email' => env('SHOP_CONTACT_EMAIL', 'info@rukn.nl'),

    // Social accounts linked in the footer. Leave one empty to hide its icon.
    'social' => [
        'instagram' => env('SHOP_SOCIAL_INSTAGRAM', 'https://www.instagram.com/rukn_30/'),
        'facebook' => env('SHOP_SOCIAL_FACEBOOK'),
        'x' => env('SHOP_SOCIAL_X'),
        'tiktok' => env('SHOP_SOCIAL_TIKTOK'),
    ],

    // Store staff are emailed about every new order.
    'order_notifications' => [
        // Comma-separated in SHOP_ORDER_NOTIFY_EMAILS; defaults to the contact address.
        'emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('SHOP_ORDER_NOTIFY_EMAILS', env('SHOP_CONTACT_EMAIL', 'info@rukn.nl')))))),

        // Language of the staff email (nl/en/ar).
        'locale' => env('SHOP_ADMIN_LOCALE', 'ar'),

        // Products at or below this stock after an order are flagged in the email.
        'low_stock_threshold' => (int) env('SHOP_LOW_STOCK_THRESHOLD', 5),
    ],

    'payments' => [
        // Pay on delivery stays available next to online payment unless disabled.
        'cod_enabled' => (bool) env('SHOP_PAYMENT_COD', true),
    ],

    'shipping' => [
        // Flat shipping fee in EUR for orders below the free-shipping threshold.
        'flat_rate' => (float) env('SHOP_SHIPPING_FLAT_RATE', 4.95),

        // Orders with a subtotal at or above this amount (EUR) ship free.
        'free_threshold' => (float) env('SHOP_FREE_SHIPPING_THRESHOLD', 50),

        // Delivery time within the Netherlands, in business days.
        'delivery_days_min' => (int) env('SHOP_DELIVERY_DAYS_MIN', 1),
        'delivery_days_max' => (int) env('SHOP_DELIVERY_DAYS_MAX', 3),
    ],

    'returns' => [
        // Days after delivery in which a customer may return an order.
        // Dutch/EU law requires at least 14 for online purchases.
        'days' => max(14, (int) env('SHOP_RETURN_DAYS', 14)),
    ],
];
