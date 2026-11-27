<?php

// Error pages (resources/views/errors).

return [
    'code' => 'Error :code',
    'home' => 'Back to home',
    'shop' => 'Browse the shop',
    'contact' => 'Contact us',
    '404' => [
        'title' => 'Page not found',
        'message' => 'This page does not exist or has moved. The product may no longer be available.',
    ],
    '403' => [
        'title' => 'No access',
        'message' => 'You do not have permission to view this page.',
    ],
    '419' => [
        'title' => 'Page expired',
        'message' => 'This page was open for too long. Go back, refresh the page and try again.',
    ],
    '429' => [
        'title' => 'Too many requests',
        'message' => 'You sent several requests in a short time. Please wait a minute and try again.',
    ],
    '500' => [
        'title' => 'Something went wrong',
        'message' => 'An error occurred on our side. Please try again in a moment, or contact us if it keeps happening.',
    ],
    '503' => [
        'title' => 'Back soon',
        'message' => 'We are carrying out maintenance. Please try again in a few minutes.',
    ],
];
