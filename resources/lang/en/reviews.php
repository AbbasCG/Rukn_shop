<?php

return [
    'stars' => '{1} 1 star|[2,*] :count stars',
    'count' => '{1} 1 review|[2,*] :count reviews',

    'form' => [
        'title' => 'Review your purchase',
        'subtitle' => 'Order #:number. Tell other customers what you think.',
        'not_delivered' => 'You can review your order once it has been delivered.',
        'nothing_to_review' => 'There is nothing left to review in this order.',
        'rating' => 'Your rating',
        'comment' => 'Your review (optional)',
        'comment_placeholder' => 'What do you like about it? How is the fit or the quality?',
        'name' => 'Name shown with your review',
        'name_hint' => 'Your email address and order are never shown.',
        'submit' => 'Send review',
        'moderation_note' => 'Reviews appear in the shop after a short check.',
        'thanks' => 'Thank you for your review! It will appear in the shop once we have checked it.',
        'already_reviewed' => 'You have already reviewed this product.',
        'your_reviews' => 'Your reviews',
    ],

    'status' => [
        'pending' => 'Waiting for approval',
        'approved' => 'Published',
        'rejected' => 'Not published',
    ],

    'product' => [
        'title' => 'Customer reviews',
        'based_on' => 'Based on :count',
        'verified' => 'Verified purchase',
        'average' => 'Average rating :rating out of 5',
    ],

    'order_cta' => [
        'title' => 'How do you like your order?',
        'text' => 'Review the products you received and help other customers choose.',
        'button' => 'Write a review',
    ],

    'admin' => [
        'title' => 'Reviews',
        'subtitle' => 'New reviews appear in the shop only after you approve them.',
        'filters' => ['pending' => 'Waiting', 'approved' => 'Published', 'rejected' => 'Rejected'],
        'empty' => 'No reviews here.',
        'product' => 'Product',
        'author' => 'Name',
        'rating' => 'Rating',
        'review' => 'Review',
        'order' => 'Order',
        'date' => 'Date',
        'no_comment' => '(stars only)',
        'approve' => 'Publish',
        'reject' => 'Reject',
        'delete' => 'Delete',
        'delete_confirm' => 'Delete this review permanently?',
        'approved' => 'The review is now visible in the shop.',
        'rejected' => 'The review will not be shown.',
        'deleted' => 'The review was deleted.',
    ],
];
