<?php

/*
| Store policies. The storefront texts (product page, FAQ, terms, cart)
| read these values, so changing them here keeps prices and promises in
| sync. Each value can be overridden from the environment on Render.
*/

return [
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
