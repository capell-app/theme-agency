<?php

declare(strict_types=1);

use Capell\AgentBridge\Http\Middleware\AuthenticateCapellAgentBridgeToken;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Capell\AgentBridge\Tests\Fixtures\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

it('ships with the legacy token hash compatibility flag defaulting to false', function (): void {
    // Read the package config file directly so the assertion reflects the shipped default
    // and is not masked by any test-environment override.
    $shippedConfiguration = require __DIR__ . '/../../config/capell-agent-bridge.php';

    expect($shippedConfiguration['accept_legacy_token_hashes'])->toBeFalse();
});

it('refuses to fall back to legacy sha256 hashes when compatibility is disabled', function (): void {
    config()->set('capell-agent-bridge.accept_legacy_token_hashes', false);

    $user = User::query()->create([
        'name' => 'Default Config Legacy User',
        'email' => 'default-config-legacy@example.test',
        'password' => 'secret',
    ]);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Default config legacy token',
        'token_hash' => CapellAgentBridgeToken::legacyHashPlainTextToken('default-legacy-token'),
        'scopes' => ['capell.pages.read'],
    ]);
    $token->user()->associate($user);
    $token->save();

    expect(CapellAgentBridgeToken::findForPlainTextToken('default-legacy-token'))->toBeNull();
});

it('rehashes a legacy token to the hmac scheme on successful use while the flag is enabled', function (): void {
    config()->set('capell-agent-bridge.accept_legacy_token_hashes', true);

    $user = User::query()->create([
        'name' => 'Legacy Rehash User',
        'email' => 'legacy-rehash@example.test',
        'password' => 'secret',
    ]);

    $legacyHash = CapellAgentBridgeToken::legacyHashPlainTextToken('rehash-me');

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Legacy rehash token',
        'token_hash' => $legacyHash,
        'scopes' => ['capell.pages.read'],
    ]);
    $token->user()->associate($user);
    $token->save();

    $response = (new AuthenticateCapellAgentBridgeToken)->handle(
        Request::create('/agent-bridge/capell', server: ['HTTP_AUTHORIZATION' => 'Bearer rehash-me']),
        fn (Request $request): Response => response('ok'),
    );

    $token->refresh();

    expect($response->getStatusCode())->toBe(200)
        ->and($token->token_hash)->toBe(CapellAgentBridgeToken::hashPlainTextToken('rehash-me'))
        ->and($token->token_hash)->not->toBe($legacyHash)
        ->and(CapellAgentBridgeToken::findForPlainTextToken('rehash-me')->is($token))->toBeTrue();
});
