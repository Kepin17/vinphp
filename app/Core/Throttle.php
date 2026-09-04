<?php

namespace App\Core;

/**
 * Opt-in rate limiting — nothing calls this automatically. Call it from a
 * controller wherever you want a limit:
 *
 *   if (!Throttle::attempt('subscribe', maxAttempts: 5, decaySeconds: 60)) {
 *       http_response_code(429);
 *       exit('Too many attempts, try again later.');
 *   }
 *
 * Session-based (per-visitor), so it's free — no new storage/dependency.
 * That also means it resets if the session does (new browser, cleared
 * cookies) — fine for slowing down casual form spam, not a substitute for
 * IP-based limiting at the edge (nginx/Cloudflare) against a real attacker.
 */
class Throttle
{
    public static function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $now = time();
        $bucket = $_SESSION['_throttle'][$key] ?? null;

        if (!$bucket || $now >= $bucket['reset_at']) {
            $bucket = ['count' => 0, 'reset_at' => $now + $decaySeconds];
        }

        if ($bucket['count'] >= $maxAttempts) {
            $_SESSION['_throttle'][$key] = $bucket;
            return false;
        }

        $bucket['count']++;
        $_SESSION['_throttle'][$key] = $bucket;

        return true;
    }
}
