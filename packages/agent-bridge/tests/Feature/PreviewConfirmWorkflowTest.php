<?php

declare(strict_types=1);

use Capell\AgentBridge\Actions\ConfirmAgentBridgeCapabilityAction;
use Capell\AgentBridge\Actions\CreateAgentBridgeTokenAction;
use Capell\AgentBridge\Actions\InvokeAgentBridgeCapabilityPreviewAction;
use Capell\AgentBridge\Data\AuthenticatedAgentBridgeClientData;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Models\CapellAgentBridgeAuditEntry;
use Capell\AgentBridge\Models\CapellAgentBridgeConfirmation;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AgentBridge\Tests\Fixtures\FakeCapabilityAction;
use Capell\AgentBridge\Tests\Fixtures\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

function registerFakeCapability(string $scope = 'capell.fake.write', ?string $policyAbility = null): void
{
    resolve(CapellAgentBridgeCapabilityRegistry::class)->register(new CapabilityData(
        key: 'capell.fake.write',
        name: 'Fake write',
        description: 'Fake mutating capability.',
        scope: $scope,
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::High,
        actionClass: FakeCapabilityAction::class,
        policyAbility: $policyAbility,
        auditEvent: 'capell_agent-bridge.fake.write',
    ));
}

/**
 * @param  array<string, mixed>  $created
 */
function agentBridgeCreatedToken(array $created): CapellAgentBridgeToken
{
    $token = $created['token'] ?? null;

    throw_unless($token instanceof CapellAgentBridgeToken, RuntimeException::class, 'Expected created agent bridge token.');

    return $token;
}

/**
 * @param  array<string, mixed>  $preview
 */
function agentBridgeConfirmationToken(array $preview): string
{
    $confirmationToken = $preview['confirmationToken'] ?? null;

    throw_unless(is_string($confirmationToken), RuntimeException::class, 'Expected agent bridge confirmation token.');

    return $confirmationToken;
}

it('previews and confirms a mutating capability with the same payload', function (): void {
    registerFakeCapability();

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'secret',
    ]);

    $created = CreateAgentBridgeTokenAction::run($user, 'Test client', ['capell.fake.write']);
    $token = agentBridgeCreatedToken($created);
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $token->getKey(),
        name: 'Test client',
        scopes: ['capell.fake.write'],
    );

    $payload = ['name' => 'Example'];

    $preview = InvokeAgentBridgeCapabilityPreviewAction::run(
        capabilityKey: 'capell.fake.write',
        payload: $payload,
        client: $client,
        token: $token,
        user: $user,
    );

    expect($preview['mode'])->toBe('preview')
        ->and($preview['confirmationToken'])->toBeString();

    $result = ConfirmAgentBridgeCapabilityAction::run(
        confirmationToken: agentBridgeConfirmationToken($preview),
        payload: $payload,
        client: $client,
        token: $token,
        user: $user,
    );

    expect($result['mode'])->toBe('confirmed')
        ->and($result['result']['message'])->toBe('Executed fake capability.')
        ->and(CapellAgentBridgeConfirmation::query()->whereNotNull('used_at')->count())->toBe(1);

    expect(fn (): array => ConfirmAgentBridgeCapabilityAction::run(
        confirmationToken: agentBridgeConfirmationToken($preview),
        payload: $payload,
        client: $client,
        token: $token,
        user: $user,
    ))->toThrow(AuthorizationException::class, 'The Agent Bridge confirmation token is invalid or expired.');
});

it('rejects confirmation when the payload changes after preview', function (): void {
    registerFakeCapability();

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'changed@example.com',
        'password' => 'secret',
    ]);

    $created = CreateAgentBridgeTokenAction::run($user, 'Test client', ['capell.fake.write']);
    $token = agentBridgeCreatedToken($created);
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $token->getKey(),
        name: 'Test client',
        scopes: ['capell.fake.write'],
    );

    $preview = InvokeAgentBridgeCapabilityPreviewAction::run(
        capabilityKey: 'capell.fake.write',
        payload: ['name' => 'Original'],
        client: $client,
        token: $token,
        user: $user,
    );

    ConfirmAgentBridgeCapabilityAction::run(
        confirmationToken: agentBridgeConfirmationToken($preview),
        payload: ['name' => 'Changed'],
        client: $client,
        token: $token,
        user: $user,
    );
})->throws(AuthorizationException::class, 'The Agent Bridge confirmation payload has changed.');

it('redacts sensitive payload and result fragments before writing audit entries', function (): void {
    registerFakeCapability();

    $user = User::query()->create([
        'name' => 'Audit User',
        'email' => 'audit-redaction@example.com',
        'password' => 'secret',
    ]);

    $created = CreateAgentBridgeTokenAction::run($user, 'Audit client', ['capell.fake.write']);
    $token = agentBridgeCreatedToken($created);
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $token->getKey(),
        name: 'Audit client',
        scopes: ['capell.fake.write'],
    );

    $payload = [
        'name' => 'Example',
        'tokenId' => (int) $token->getKey(),
        'accessToken' => 'secret-access-token',
        'password' => 'secret-password',
        'authorization' => 'Bearer secret-header-token',
        'adminUrl' => 'https://example.test/admin/pages/1/edit?expires=123&signature=abc',
        'prompt' => [
            'is_private' => true,
            'prompt' => 'Private launch instructions',
        ],
    ];

    $preview = InvokeAgentBridgeCapabilityPreviewAction::run(
        capabilityKey: 'capell.fake.write',
        payload: $payload,
        client: $client,
        token: $token,
        user: $user,
    );

    $result = ConfirmAgentBridgeCapabilityAction::run(
        confirmationToken: agentBridgeConfirmationToken($preview),
        payload: $payload,
        client: $client,
        token: $token,
        user: $user,
    );

    $resultPayload = data_get($result, 'result.data.payload');

    throw_unless(is_array($resultPayload), RuntimeException::class, 'Expected agent bridge result payload array.');

    expect($resultPayload['accessToken'] ?? null)->toBe('secret-access-token');

    $auditEntries = CapellAgentBridgeAuditEntry::query()
        ->orderBy('id')
        ->get();

    expect($auditEntries)->toHaveCount(2);

    foreach ($auditEntries as $auditEntry) {
        $auditResultPayload = data_get($auditEntry->result, 'data.payload');

        throw_unless(is_array($auditResultPayload), RuntimeException::class, 'Expected redacted audit result payload array.');

        expect($auditEntry->payload)
            ->toMatchArray([
                'name' => 'Example',
                'tokenId' => (int) $token->getKey(),
                'accessToken' => '[redacted]',
                'password' => '[redacted]',
                'authorization' => '[redacted]',
                'adminUrl' => '[redacted]',
                'prompt' => ['[redacted]'],
            ])
            ->and($auditResultPayload['accessToken'] ?? null)->toBe('[redacted]')
            ->and($auditResultPayload['password'] ?? null)->toBe('[redacted]')
            ->and($auditResultPayload['authorization'] ?? null)->toBe('[redacted]')
            ->and($auditResultPayload['adminUrl'] ?? null)->toBe('[redacted]')
            ->and($auditResultPayload['prompt'] ?? null)->toBe(['[redacted]']);
    }
});

it('rejects confirmation replay across users', function (): void {
    registerFakeCapability();

    $user = User::query()->create([
        'name' => 'Original User',
        'email' => 'original-confirm@example.com',
        'password' => 'secret',
    ]);
    $otherUser = User::query()->create([
        'name' => 'Other User',
        'email' => 'other-confirm@example.com',
        'password' => 'secret',
    ]);

    $created = CreateAgentBridgeTokenAction::run($user, 'Original client', ['capell.fake.write']);
    $token = agentBridgeCreatedToken($created);
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $token->getKey(),
        name: 'Original client',
        scopes: ['capell.fake.write'],
    );

    $preview = InvokeAgentBridgeCapabilityPreviewAction::run(
        capabilityKey: 'capell.fake.write',
        payload: ['name' => 'Original'],
        client: $client,
        token: $token,
        user: $user,
    );

    ConfirmAgentBridgeCapabilityAction::run(
        confirmationToken: agentBridgeConfirmationToken($preview),
        payload: ['name' => 'Original'],
        client: $client,
        token: $token,
        user: $otherUser,
    );
})->throws(AuthorizationException::class, 'The Agent Bridge confirmation token does not belong to this user.');

it('rejects policy protected capability previews when no authenticated user is available', function (): void {
    registerFakeCapability(policyAbility: 'preview fake capability');

    Gate::define('preview fake capability', static fn (User $user): bool => true);

    $created = CreateAgentBridgeTokenAction::run(User::query()->create([
        'name' => 'Token Owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]), 'Test client', ['capell.fake.write']);
    $token = agentBridgeCreatedToken($created);
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $token->getKey(),
        name: 'Test client',
        scopes: ['capell.fake.write'],
    );

    InvokeAgentBridgeCapabilityPreviewAction::run(
        capabilityKey: 'capell.fake.write',
        payload: ['name' => 'Original'],
        client: $client,
        token: $token,
    );
})->throws(AuthorizationException::class, 'Agent Bridge policy ability [preview fake capability] requires an authenticated user.');
