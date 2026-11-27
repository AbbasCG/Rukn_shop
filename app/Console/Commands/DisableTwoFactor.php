<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Way back in when an admin cannot receive codes (e.g. email broken):
 * run on the server, which only the shop owner can reach.
 */
class DisableTwoFactor extends Command
{
    protected $signature = 'two-factor:disable {email : The admin\'s email address}';

    protected $description = 'Turn off two-step verification for an account (if its codes no longer arrive)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('No account with this email address.');

            return self::FAILURE;
        }

        $user->forceFill(['two_factor_enabled_at' => null])->save();
        $this->info("Two-step verification is off for {$user->email}. Log in with the password and turn it on again from the profile page.");

        return self::SUCCESS;
    }
}
