<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Http\Middleware;

use Capell\AgentBridge\Data\AuthenticatedAgentBridgeClientData;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateCapellAgentBridgeToken
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = (string) $request->bearerToken();

        if ($plainTextToken === '') {
            return $this->unauthorized('Missing Agent Bridge bearer token.');
        }

        $token = CapellAgentBridgeToken::findForPlainTextToken($plainTextToken);

        if (! $token instanceof CapellAgentBridgeToken || ! $token->isUsable()) {
            return $this->unauthorized('Invalid Agent Bridge bearer token.');
        }

        $user = $token->user;

        if ($user === null) {
            return $this->unauthorized('Agent Bridge token is not linked to a user.');
        }

        $guard = config('capell-agent-bridge.site_auth_guard', 'web');
        $authGuard = Auth::guard($guard);
        $authGuard->setUser($user);
        Auth::shouldUse($guard);

        $tokenUpdates = [];

        if ($this->shouldUpdateLastUsedAt($token)) {
            $tokenUpdates['last_used_at'] = now();
        }

        if ($token->hasLegacyHashForPlainTextToken($plainTextToken)) {
            $tokenUpdates['token_hash'] = CapellAgentBridgeToken::hashPlainTextToken($plainTextToken);
        }

        if ($tokenUpdates !== []) {
            $token->forceFill($tokenUpdates)->save();
        }

        app()->instance(CapellAgentBridgeToken::class, $token);
        app()->instance(AuthenticatedAgentBridgeClientData::class, new AuthenticatedAgentBridgeClientData(
            tokenId: (int) $token->getKey(),
            name: $token->name,
            scopes: $token->scopes,
        ));

        return $next($request);
    }

    private function shouldUpdateLastUsedAt(CapellAgentBridgeToken $token): bool
    {
        $throttleMinutes = (int) config('capell-agent-bridge.last_used_throttle_minutes', 5);

        if ($throttleMinutes <= 0) {
            return true;
        }

        $lastUsed = $token->last_used_at;

        if (! $lastUsed instanceof CarbonImmutable) {
            return true;
        }

        return $lastUsed->diffInMinutes(now()) >= $throttleMinutes;
    }

    private function unauthorized(string $message): Response
    {
        return response($message, 401)
            ->header('WWW-Authenticate', 'Bearer realm="capell-agent-bridge", error="invalid_token"');
    }
}
