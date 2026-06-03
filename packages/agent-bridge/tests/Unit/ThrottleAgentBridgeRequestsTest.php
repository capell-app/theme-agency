<?php

declare(strict_types=1);

use Capell\AgentBridge\Http\Middleware\ThrottleAgentBridgeRequests;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Capell\AgentBridge\Tests\Fixtures\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

it('allows requests within the rate limit', function (): void {
    $user = User::query()->create([
        'name' => 'Throttle User',
        'email' => 'throttle@example.test',
        'password' => 'secret',
    ]);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Throttle token',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('throttle-token'),
        'scopes' => ['*'],
    ]);
    $token->user()->associate($user);
    $token->save();

    app()->instance(CapellAgentBridgeToken::class, $token);
    config()->set('capell-agent-bridge.rate_limit_per_minute', 60);

    $response = (new ThrottleAgentBridgeRequests)->handle(
        Request::create('/agent-bridge/capell'),
        fn (Request $request): Response => response('ok'),
    );

    expect($response->getStatusCode())->toBe(200);
});

it('rejects requests exceeding the rate limit with 429', function (): void {
    $user = User::query()->create([
        'name' => 'Throttle Limit User',
        'email' => 'throttle-limit@example.test',
        'password' => 'secret',
    ]);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Rate limit token',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('rate-limit-token'),
        'scopes' => ['*'],
    ]);
    $token->user()->associate($user);
    $token->save();

    app()->instance(CapellAgentBridgeToken::class, $token);
    config()->set('capell-agent-bridge.rate_limit_per_minute', 3);

    $middleware = new ThrottleAgentBridgeRequests;
    $request = Request::create('/agent-bridge/capell');
    $nextHandler = fn (Request $request): Response => response('ok');

    for ($attempt = 0; $attempt < 3; $attempt++) {
        $middleware->handle($request, $nextHandler);
    }

    $response = $middleware->handle($request, $nextHandler);

    expect($response->getStatusCode())->toBe(429)
        ->and($response->getContent())->toContain('rate limit exceeded')
        ->and($response->headers->has('Retry-After'))->toBeTrue();

    RateLimiter::clear('capell-agent-bridge:token:' . $token->getKey());
});

it('passes through when rate limiting is disabled', function (): void {
    $user = User::query()->create([
        'name' => 'No Limit User',
        'email' => 'no-limit@example.test',
        'password' => 'secret',
    ]);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'No limit token',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('no-limit-token'),
        'scopes' => ['*'],
    ]);
    $token->user()->associate($user);
    $token->save();

    app()->instance(CapellAgentBridgeToken::class, $token);
    config()->set('capell-agent-bridge.rate_limit_per_minute', 0);

    $response = (new ThrottleAgentBridgeRequests)->handle(
        Request::create('/agent-bridge/capell'),
        fn (Request $request): Response => response('ok'),
    );

    expect($response->getStatusCode())->toBe(200);
});

it('passes through when the global rate limiting flag is disabled', function (): void {
    $user = User::query()->create([
        'name' => 'Flag Disabled User',
        'email' => 'flag-disabled@example.test',
        'password' => 'secret',
    ]);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Flag disabled token',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('flag-disabled-token'),
        'scopes' => ['*'],
    ]);
    $token->user()->associate($user);
    $token->save();

    app()->instance(CapellAgentBridgeToken::class, $token);
    config()->set('capell-agent-bridge.rate_limit_enabled', false);
    config()->set('capell-agent-bridge.rate_limit_per_minute', 1);

    $middleware = new ThrottleAgentBridgeRequests;
    $request = Request::create('/agent-bridge/capell');
    $nextHandler = fn (Request $request): Response => response('ok');

    $middleware->handle($request, $nextHandler);
    $response = $middleware->handle($request, $nextHandler);

    expect($response->getStatusCode())->toBe(200);
});

it('passes through when no token is bound in the container', function (): void {
    $response = (new ThrottleAgentBridgeRequests)->handle(
        Request::create('/agent-bridge/capell'),
        fn (Request $request): Response => response('ok'),
    );

    expect($response->getStatusCode())->toBe(200);
});
