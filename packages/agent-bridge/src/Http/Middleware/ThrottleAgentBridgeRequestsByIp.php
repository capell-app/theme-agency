<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-IP request throttle that runs ahead of token authentication.
 *
 * The token-keyed throttle ({@see ThrottleAgentBridgeRequests}) can only run after a valid
 * token has been resolved, so failed-authentication attempts are invisible to it and would
 * otherwise be unthrottled. This middleware caps requests per source IP regardless of whether
 * authentication succeeds, blunting credential-stuffing and brute-force attempts against the
 * Agent Bridge endpoints.
 */
final class ThrottleAgentBridgeRequestsByIp
{
    private const int DEFAULT_MAX_ATTEMPTS_PER_MINUTE = 120;

    private const int DECAY_SECONDS = 60;

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('capell-agent-bridge.rate_limit_enabled', true)) {
            return $next($request);
        }

        $configuredMaxAttempts = config(
            'capell-agent-bridge.rate_limit_per_ip_per_minute',
            self::DEFAULT_MAX_ATTEMPTS_PER_MINUTE,
        );

        $maxAttempts = is_numeric($configuredMaxAttempts)
            ? (int) $configuredMaxAttempts
            : self::DEFAULT_MAX_ATTEMPTS_PER_MINUTE;

        if ($maxAttempts <= 0) {
            return $next($request);
        }

        $rateLimitKey = 'capell-agent-bridge:ip:' . sha1((string) $request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);

            return response(
                __('capell-agent-bridge::messages.rate_limited', ['seconds' => $retryAfter]),
                429,
            )->header('Retry-After', (string) $retryAfter);
        }

        RateLimiter::hit($rateLimitKey, self::DECAY_SECONDS);

        return $next($request);
    }
}
