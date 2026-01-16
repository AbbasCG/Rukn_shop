<?php

return [
    'title' => 'Checkout',
    'subtitle' => 'Complete your information to place the order.',

    'form' => [
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'country' => 'Country',
        'address1' => 'Address line 1',
        'address2' => 'Address line 2',
        'postal_code' => 'Postal code',
        'city' => 'City',
        'shipping_method' => 'Shipping method',
        'shipping_options' => [
            'flat' => 'Flat Rate (Free)',
        ],
        'payment_method' => 'Payment method',
        'payment_options' => [
            'cod' => 'Pay on Delivery',
            'mock' => 'Mock Payment',
        ],
        'submit' => 'Place Order',
    ],

    'summary' => [
        'title' => 'Order Summary',
        'subtotal' => 'Subtotal',
        'shipping' => 'Shipping',
        'total' => 'Total',
        'back_to_cart' => 'Back to Cart',
    ],

    'flash' => [
        'empty_cart' => 'Your cart is empty.',
        'failed' => 'Failed to place order. Please try again.',
    ],
];
