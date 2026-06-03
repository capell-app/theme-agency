<?php

declare(strict_types=1);

use Capell\AgentBridge\Http\Middleware\AuthenticateCapellAgentBridgeToken;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Capell\AgentBridge\Tests\Fixtures\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

it('rejects requests without an agent bridge bearer token', function (): void {
    $response = (new AuthenticateCapellAgentBridgeToken)->handle(
        Request::create('/agent-bridge/capell'),
        fn (Request $request): never => throw new RuntimeException('Next middleware should not be called.'),
    );

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getContent())->toBe('Missing Agent Bridge bearer token.')
        ->and($response->headers->get('WWW-Authenticate'))->toBe('Bearer realm="capell-agent-bridge", error="invalid_token"');
});

it('authenticates valid agent bridge bearer tokens and binds client context', function (): void {
    $user = User::query()->create([
        'name' => 'Middleware User',
        'email' => 'middleware@example.test',
        'password' => 'secret',
    ]);
    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Middleware token',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('plain-token'),
        'scopes' => ['capell.pages.read'],
    ]);
    $token->user()->associate($user);
    $token->save();

    $response = (new AuthenticateCapellAgentBridgeToken)->handle(
        Request::create('/agent-bridge/capell', server: ['HTTP_AUTHORIZATION' => 'Bearer plain-token']),
        fn (Request $request): Response => response('ok'),
    );

    expect($response->getStatusCode())->toBe(200)
        ->and($token->refresh()->last_used_at)->not->toBeNull()
        ->and(resolve(CapellAgentBridgeToken::class)->is($token))->toBeTrue();
});

it('hashes agent bridge tokens with the application key', function (): void {
    expect(CapellAgentBridgeToken::hashPlainTextToken('plain-token'))
        ->toBe(hash_hmac('sha256', 'plain-token', (string) config('app.key')))
        ->not->toBe(CapellAgentBridgeToken::legacyHashPlainTextToken('plain-token'));
});

it('authenticates legacy sha256 token hashes and upgrades them on use', function (): void {
    $user = User::query()->create([
        'name' => 'Legacy Middleware User',
        'email' => 'legacy-middleware@example.test',
        'password' => 'secret',
    ]);
    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Legacy middleware token',
        'token_hash' => CapellAgentBridgeToken::legacyHashPlainTextToken('legacy-token'),
        'scopes' => ['capell.pages.read'],
    ]);
    $token->user()->associate($user);
    $token->save();

    $response = (new AuthenticateCapellAgentBridgeToken)->handle(
        Request::create('/agent-bridge/capell', server: ['HTTP_AUTHORIZATION' => 'Bearer legacy-token']),
        fn (Request $request): Response => response('ok'),
    );

    expect($response->getStatusCode())->toBe(200)
        ->and($token->refresh()->token_hash)->toBe(CapellAgentBridgeToken::hashPlainTextToken('legacy-token'));
});

it('rejects legacy sha256 token hashes when the compatibility flag is disabled', function (): void {
    config()->set('capell-agent-bridge.accept_legacy_token_hashes', false);

    $user = User::query()->create([
        'name' => 'Rejected Legacy User',
        'email' => 'rejected-legacy@example.test',
        'password' => 'secret',
    ]);
    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Rejected legacy token',
        'token_hash' => CapellAgentBridgeToken::legacyHashPlainTextToken('rejected-legacy-token'),
        'scopes' => ['capell.pages.read'],
    ]);
    $token->user()->associate($user);
    $token->save();

    $response = (new AuthenticateCapellAgentBridgeToken)->handle(
        Request::create('/agent-bridge/capell', server: ['HTTP_AUTHORIZATION' => 'Bearer rejected-legacy-token']),
        fn (Request $request): Response => response('ok'),
    );

    expect($response->getStatusCode())->toBe(401);
});

it('rejects revoked or disabled tokens', function (): void {
    $user = User::query()->create([
        'name' => 'Revoked Middleware User',
        'email' => 'revoked-middleware@example.test',
        'password' => 'secret',
    ]);
    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Revoked middleware token',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('revoked-token'),
        'scopes' => ['capell.pages.read'],
        'is_enabled' => false,
        'revoked_at' => now(),
    ]);
    $token->user()->associate($user);
    $token->save();

    $response = (new AuthenticateCapellAgentBridgeToken)->handle(
        Request::create('/agent-bridge/capell', server: ['HTTP_AUTHORIZATION' => 'Bearer revoked-token']),
        fn (Request $request): Response => response('ok'),
    );

    expect($response->getStatusCode())->toBe(401);
});

it('skips last_used_at update when recently used within throttle window', function (): void {
    config()->set('capell-agent-bridge.last_used_throttle_minutes', 5);

    $user = User::query()->create([
        'name' => 'Throttle User',
        'email' => 'throttle-last-used@example.test',
        'password' => 'secret',
    ]);

    $recentTimestamp = CarbonImmutable::now()->subMinutes(2);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Throttle last_used token',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('throttle-last-used-token'),
        'scopes' => ['capell.pages.read'],
        'last_used_at' => $recentTimestamp,
    ]);
    $token->user()->associate($user);
    $token->save();

    (new AuthenticateCapellAgentBridgeToken)->handle(
        Request::create('/agent-bridge/capell', server: ['HTTP_AUTHORIZATION' => 'Bearer throttle-last-used-token']),
        fn (Request $request): Response => response('ok'),
    );

    $token->refresh();

    expect($token->last_used_at->format('Y-m-d H:i:s'))->toBe($recentTimestamp->format('Y-m-d H:i:s'));
});

it('updates last_used_at when outside the throttle window', function (): void {
    config()->set('capell-agent-bridge.last_used_throttle_minutes', 5);

    $user = User::query()->create([
        'name' => 'Stale Throttle User',
        'email' => 'stale-throttle@example.test',
        'password' => 'secret',
    ]);

    $staleTimestamp = CarbonImmutable::now()->subMinutes(10);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Stale last_used token',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('stale-last-used-token'),
        'scopes' => ['capell.pages.read'],
        'last_used_at' => $staleTimestamp,
    ]);
    $token->user()->associate($user);
    $token->save();

    (new AuthenticateCapellAgentBridgeToken)->handle(
        Request::create('/agent-bridge/capell', server: ['HTTP_AUTHORIZATION' => 'Bearer stale-last-used-token']),
        fn (Request $request): Response => response('ok'),
    );

    $token->refresh();

    expect($token->last_used_at->format('Y-m-d H:i:s'))->not->toBe($staleTimestamp->format('Y-m-d H:i:s'));
});
