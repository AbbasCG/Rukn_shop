<?php

return [
    'title' => 'My Orders',
    'subtitle' => 'View your recent purchases and their status.',

    'empty' => [
        'title' => 'You have not placed any orders yet.',
        'subtitle' => 'Start shopping to see your orders here.',
        'button' => 'Start Shopping',
    ],

    'table' => [
        'order_number' => 'Order #',
        'date' => 'Date',
        'total' => 'Total',
        'status' => 'Status',
        'actions' => 'Actions',
    ],

    'status' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    'actions' => [
        'view_details' => 'View Details',
        'back_to_orders' => 'Back to My Orders',
        'back_to_profile' => 'Back to Profile',
        'continue_shopping' => 'Continue Shopping',
    ],

    'details' => [
        'title' => 'Order Details',
        'summary' => 'Order Summary',
        'shipping_info' => 'Shipping Information',
        'items' => 'Order Items',
        'status_tracking' => 'Order Status',
        'notes' => 'Order Notes',
    ],

    'summary' => [
        'subtotal' => 'Subtotal',
        'shipping' => 'Shipping',
        'tax' => 'Tax',
        'discount' => 'Discount',
        'total' => 'Total',
        'payment_method' => 'Payment Method',
        'payment_status' => 'Payment Status',
    ],

    'shipping' => [
        'name' => 'Name',
        'address' => 'Address',
        'postal_code' => 'Postal Code',
        'city' => 'City',
        'country' => 'Country',
        'phone' => 'Phone',
    ],

    'items' => [
        'product' => 'Product',
        'sku' => 'SKU',
        'quantity' => 'Quantity',
        'price_per_unit' => 'Price per unit',
        'item_total' => 'Item Total',
        'no_items' => 'No items found in this order.',
    ],

    'tracking' => [
        'title' => 'Order Status',
        'live_tracking' => 'Live tracking',
        'current_status' => 'Current status',
        'in_progress' => 'In progress',
        'update_message' => 'We\'ll update you as your order moves to the next step.',
        'steps' => [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
        ],
    ],

    'confirmation' => [
        'thank_you' => 'Thank you for your order!',
        'received' => 'Your order has been received and is now being processed.',
        'summary' => 'Order Summary',
        'order_number' => 'Order #',
    ],
];
