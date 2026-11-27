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
            'flat' => 'Standard shipping',
        ],
        'payment_method' => 'Payment method',
        'payment_options' => [
            'cod' => 'Pay on Delivery',
            'mollie' => 'Pay online',
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
        'stock_changed' => ':product has only :count left in stock. Please update your cart.',
        'unavailable' => ':product is no longer available. Please remove it from your cart.',
    ],

    'confirmation' => [
        'view_order' => 'View My Order',
        'continue_shopping' => 'Continue Shopping',
        'placed_with_number' => 'Your order :number has been successfully placed.',
    ],
    'have_account' => 'Have an account? Log in',
    'payment' => [
        'description' => 'Order #:number at Rukn',
        'start_failed' => 'The online payment could not be started. Your cart is unchanged; please try again or choose another payment method.',
        'online' => 'Pay online',
        'online_desc' => 'iDEAL, card, Bancontact and more. You choose on the secure Mollie page.',
        'cod' => 'Pay on delivery',
        'cod_desc' => 'Pay when your order is delivered.',
        'pending_title' => 'Checking your payment…',
        'pending_text' => 'Your bank is confirming the payment. This page refreshes by itself.',
        'failed_title' => 'Payment not completed',
        'failed_text' => 'The payment was cancelled or did not go through. Your order is reserved; you can try again.',
        'retry' => 'Try the payment again',
        'expired_title' => 'Order cancelled',
        'expired_text' => 'The payment was not completed in time, so the order was cancelled. Nothing has been charged.',
        'back_to_shop' => 'Back to the shop',
        'paid_online' => 'Paid online',
    ],
];
