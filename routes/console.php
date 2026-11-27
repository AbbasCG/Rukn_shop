<?php

use Illuminate\Support\Facades\Schedule;

/*
| Scheduled tasks. On Hostinger a single cron job runs "php artisan
| schedule:run" every minute (see README); it starts everything below.
*/

// Send the queued emails (shared hosting cannot keep a worker running).
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=4')
    ->everyMinute()
    ->withoutOverlapping(5);

// Ask buyers for a review a few days after delivery.
Schedule::command('reviews:send-requests')->hourly()->withoutOverlapping();
