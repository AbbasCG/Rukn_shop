<?php

return [
    'sidebar' => [
        'brand' => 'Admin Panel',
        'sections' => [
            'menu' => 'Menu',
            'coming_soon' => 'Coming Soon',
        ],
        'items' => [
            'dashboard' => 'Dashboard',
            'products' => 'Products',
            'categories' => 'Categories',
            'orders' => 'Orders',
            'users' => 'Users',
            'settings' => 'Settings',
            'reports' => 'Reports',
            'logout' => 'Log Out',
        ],
        'tooltips' => [
            'close_sidebar' => 'Close sidebar',
        ],
    ],
    'footer' => [
        'logged_in_as' => 'Logged in as',
        'back_to_shop' => 'Back to Shop',
        'rights' => 'All rights reserved',
        'powered_by' => 'Powered by :name',
        'links' => [
            'privacy' => 'Privacy Policy',
            'terms' => 'Terms',
            'support' => 'Support',
            'docs' => 'Documentation',
        ],
    ],
    'dashboard' => [
        'page_title' => 'Dashboard',
        'title' => 'Welcome back, :name 👋',
        'cards' => [
            'total_products' => 'Total Products',
            'in_inventory' => 'In inventory',
            'total_categories' => 'Total Categories',
            'active_categories' => 'Active categories',
            'total_orders' => 'Total Orders',
            'all_time' => 'All time',
            'total_revenue' => 'Total Revenue',
            'from_paid_orders' => 'From paid orders',
        ],
        'sales' => [
            'title' => 'Sales Last 12 Months',
            'subtitle' => 'Revenue overview and trends',
            'chart_placeholder' => 'Chart will be rendered here',
        ],
        'quick_actions' => [
            'title' => 'Quick Actions',
            'subtitle' => 'Common tasks',
            'add_new_product' => 'Add New Product',
            'add_new_category' => 'Add New Category',
            'add_new_user' => 'Add New User',
            'view_all_orders' => 'View All Orders',
        ],
        'low_stock' => [
            'title' => 'Low Stock Alerts',
            'items_left' => ':count left',
            'all_well_stocked' => '✓ All products are well stocked',
        ],
        'recent_orders' => [
            'title' => 'Recent Orders',
            'subtitle' => 'Latest customer orders',
            'guest' => 'Guest',
            'view_action' => 'View',
            'empty' => 'No orders yet',
            'table' => [
                'order_id' => 'Order ID',
                'customer' => 'Customer',
                'total' => 'Total',
                'status' => 'Status',
                'date' => 'Date',
                'action' => 'Action',
            ],
        ],
        'statuses' => [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ],
    ],
];
