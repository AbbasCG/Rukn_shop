<?php

namespace App\Support;

use App\Mail\TwoFactorCode;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Two-step verification by email: a 6-digit code, kept (hashed) in the
 * session together with what it is for.
 *
 * Purposes: "login" (after a correct password) and "enable" (confirming
 * that codes arrive before the admin turns it on).
 */
class TwoFactor
{
    public const MINUTES = 10;           // a code works this long

    public const MAX_ATTEMPTS = 5;       // wrong codes before a new one is needed

    public const RESEND_SECONDS = 60;    // wait between two emails

    public const MAX_SENDS = 5;          // emails per login attempt / setup

    private const SESSION = 'two_factor';

    /**
     * Create a code for $purpose, remember it in the session and email it.
     * Sent right away (not via the queue): the admin is waiting for it.
     */
    public static function send(User $user, string $purpose, array $extra = []): void
    {
        $previous = self::pending($purpose);
        $sends = $previous && (int) $previous['user_id'] === $user->id ? (int) $previous['sends'] : 0;

        $code = (string) random_int(100000, 999999);

        session([self::SESSION . '.' . $purpose => $extra + [
            'user_id' => $user->id,
            'hash' => self::hash($code),
            'expires_at' => now()->addMinutes(self::MINUTES)->getTimestamp(),
            'sent_at' => now()->getTimestamp(),
            'attempts' => 0,
            'sends' => $sends + 1,
        ]]);

        Mail::to($user)->send(new TwoFactorCode($user, $code, app()->getLocale()));
    }

    /** @return array<string, mixed>|null */
    public static function pending(string $purpose): ?array
    {
        $state = session(self::SESSION . '.' . $purpose);

        return is_array($state) ? $state : null;
    }

    /**
     * Check a code. Returns "ok", "wrong", "expired" or "too_many".
     * A correct code can be used once.
     */
    public static function check(string $purpose, string $code): string
    {
        $state = self::pending($purpose);
        if (! $state || empty($state['hash']) || now()->getTimestamp() > (int) $state['expires_at']) {
            return 'expired';
        }
        if ((int) $state['attempts'] >= self::MAX_ATTEMPTS) {
            return 'too_many';
        }

        if (hash_equals($state['hash'], self::hash(preg_replace('/\D/', '', $code)))) {
            // One use only; keep the rest of the state (e.g. "remember").
            session([self::SESSION . '.' . $purpose . '.hash' => null]);

            return 'ok';
        }

        session([self::SESSION . '.' . $purpose . '.attempts' => (int) $state['attempts'] + 1]);

        return (int) $state['attempts'] + 1 >= self::MAX_ATTEMPTS ? 'too_many' : 'wrong';
    }

    /** Seconds until another email may be sent (0 = now); null when the limit is reached. */
    public static function resendWait(string $purpose): ?int
    {
        $state = self::pending($purpose);
        if (! $state) {
            return 0;
        }
        if ((int) $state['sends'] >= self::MAX_SENDS) {
            return null;
        }

        return max(0, (int) $state['sent_at'] + self::RESEND_SECONDS - now()->getTimestamp());
    }

    public static function forget(string $purpose): void
    {
        session()->forget(self::SESSION . '.' . $purpose);
    }

    private static function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }
}
