<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Health of the background email queue (database driver), for the admin.
 */
class MailQueue
{
    /** Emails waiting longer than this suggest the worker is not running. */
    public const STUCK_AFTER_MINUTES = 15;

    public static function usesDatabase(): bool
    {
        return config('queue.default') === 'database';
    }

    /** Emails that failed after all attempts. */
    public static function failedCount(): int
    {
        return Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
    }

    /** Emails still waiting although they should have gone out long ago. */
    public static function stuckCount(): int
    {
        if (! self::usesDatabase() || ! Schema::hasTable('jobs')) {
            return 0;
        }

        return DB::table('jobs')
            ->whereNull('reserved_at')
            ->where('available_at', '<', now()->subMinutes(self::STUCK_AFTER_MINUTES)->getTimestamp())
            ->count();
    }
}
