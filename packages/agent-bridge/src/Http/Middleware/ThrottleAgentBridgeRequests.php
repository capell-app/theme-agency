<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Http\Middleware;

use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class ThrottleAgentBridgeRequests
{
    private const int DEFAULT_MAX_ATTEMPTS_PER_MINUTE = 60;

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = app()->bound(CapellAgentBridgeToken::class)
            ? resolve(CapellAgentBridgeToken::class)
            : null;

        if (! $token instanceof CapellAgentBridgeToken) {
            return $next($request);
        }

        if (! (bool) config('capell-agent-bridge.rate_limit_enabled', true)) {
            return $next($request);
        }

        $rateLimitKey = 'capell-agent-bridge:token:' . $token->getKey();
        $maxAttempts = (int) config('capell-agent-bridge.rate_limit_per_minute', self::DEFAULT_MAX_ATTEMPTS_PER_MINUTE);

        if ($maxAttempts <= 0) {
            return $next($request);
        }

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);

            return response('Agent Bridge rate limit exceeded. Try again in ' . $retryAfter . ' seconds.', 429)
                ->header('Retry-After', (string) $retryAfter);
        }

        RateLimiter::hit($rateLimitKey, 60);

        return $next($request);
    }
}
