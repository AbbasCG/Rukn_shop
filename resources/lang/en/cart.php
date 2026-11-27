<?php

return [
    'title' => 'Your Cart',

    'empty' => [
        'title' => 'Your cart is empty',
        'subtitle' => 'Browse products and add your favorites to the cart.',
        'action' => 'Continue Shopping',
    ],

    'item' => [

        'unavailable' => 'No longer available. Please remove it from your cart.',

        'price_changed' => 'Price changed since you added it (was :old).',
        'remove_confirm' => 'Remove this item?',
        'remove' => 'Remove',
    ],

    'actions' => [

        'checkout_guest' => 'Checkout as guest',

        'login_checkout' => 'Log in for faster checkout',
        'continue' => 'Continue Shopping',
        'clear' => 'Clear Cart',
        'proceed' => 'Proceed to Checkout',
        'signin' => 'Sign in to Checkout',
    ],

    'summary' => [
        'title' => 'Order Summary',
        'subtotal' => 'Subtotal',
        'shipping' => 'Shipping',
        'free' => 'Free',
        'free_shipping_hint' => 'Add :amount more for free shipping.',
        'total' => 'Total',
    ],

    'flash' => [
        'added' => 'Product added to cart!',
        'updated' => 'Cart updated.',
        'removed' => 'Item removed from cart.',
        'cleared' => 'Cart cleared.',
        'empty' => 'Your cart is empty.',
        'restored' => 'Your cart was restored.',
        'restored_partial' => 'Some items were unavailable and removed.',
        'out_of_stock' => 'Sorry, this product is out of stock.',
        'choose_variant' => 'Please choose a size and colour first.',
        'stock_limited' => 'Only :count in stock. Your cart quantity was adjusted.',
    ],
    'coupon' => [
        'label' => 'Discount code',
        'placeholder' => 'Enter code',
        'apply' => 'Apply',
        'remove' => 'Remove',
        'invalid' => 'This code does not exist or is no longer valid.',
        'applied' => 'Code :code applied.',
        'removed' => 'The discount code has been removed.',
        'active_free_shipping' => 'Code :code: free shipping',
        'below_minimum' => 'Code :code gives free shipping from :amount in products.',
        'shipping_with_code' => 'Free (code :code)',
    ],
];
