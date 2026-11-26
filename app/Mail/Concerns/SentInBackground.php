<?php

namespace App\Mail\Concerns;

use Illuminate\Support\Facades\Log;

/**
 * Settings for shop emails that are sent by the queue worker instead of
 * during the customer's request (the mailables also implement ShouldQueue).
 * A temporary mail-server problem is retried; after the last attempt the
 * email lands in failed_jobs, is logged, and shows up in the admin.
 */
trait SentInBackground
{
    /** Attempts before giving up. */
    public $tries = 4;

    /** Seconds to wait before attempts 2, 3 and 4. */
    public $backoff = [60, 300, 1800];

    /** One attempt may take at most this long (slow SMTP servers). */
    public $timeout = 60;

    public function failed(\Throwable $e): void
    {
        Log::error('Email could not be sent after several attempts.', [
            'email' => static::class,
            'error' => $e->getMessage(),
        ]);
    }
}
