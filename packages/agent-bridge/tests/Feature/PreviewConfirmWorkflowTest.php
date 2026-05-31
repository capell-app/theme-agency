<?php

declare(strict_types=1);

use Capell\AgentBridge\Actions\ConfirmAgentBridgeCapabilityAction;
use Capell\AgentBridge\Actions\CreateAgentBridgeTokenAction;
use Capell\AgentBridge\Actions\InvokeAgentBridgeCapabilityPreviewAction;
use Capell\AgentBridge\Data\AuthenticatedAgentBridgeClientData;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Models\CapellAgentBridgeConfirmation;
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

it('previews and confirms a mutating capability with the same payload', function (): void {
    registerFakeCapability();

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'secret',
    ]);

    $created = CreateAgentBridgeTokenAction::run($user, 'Test client', ['capell.fake.write']);
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $created['token']->getKey(),
        name: 'Test client',
        scopes: ['capell.fake.write'],
    );

    $payload = ['name' => 'Example'];

    $preview = InvokeAgentBridgeCapabilityPreviewAction::run(
        capabilityKey: 'capell.fake.write',
        payload: $payload,
        client: $client,
        token: $created['token'],
        user: $user,
    );

    expect($preview['mode'])->toBe('preview')
        ->and($preview['confirmationToken'])->toBeString();

    $result = ConfirmAgentBridgeCapabilityAction::run(
        confirmationToken: $preview['confirmationToken'],
        payload: $payload,
        client: $client,
        token: $created['token'],
        user: $user,
    );

    expect($result['mode'])->toBe('confirmed')
        ->and($result['result']['message'])->toBe('Executed fake capability.')
        ->and(CapellAgentBridgeConfirmation::query()->whereNotNull('used_at')->count())->toBe(1);

    expect(fn (): array => ConfirmAgentBridgeCapabilityAction::run(
        confirmationToken: $preview['confirmationToken'],
        payload: $payload,
        client: $client,
        token: $created['token'],
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
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $created['token']->getKey(),
        name: 'Test client',
        scopes: ['capell.fake.write'],
    );

    $preview = InvokeAgentBridgeCapabilityPreviewAction::run(
        capabilityKey: 'capell.fake.write',
        payload: ['name' => 'Original'],
        client: $client,
        token: $created['token'],
        user: $user,
    );

    ConfirmAgentBridgeCapabilityAction::run(
        confirmationToken: $preview['confirmationToken'],
        payload: ['name' => 'Changed'],
        client: $client,
        token: $created['token'],
        user: $user,
    );
})->throws(AuthorizationException::class, 'The Agent Bridge confirmation payload has changed.');

it('rejects policy protected capability previews when no authenticated user is available', function (): void {
    registerFakeCapability(policyAbility: 'preview fake capability');

    Gate::define('preview fake capability', static fn (User $user): bool => true);

    $created = CreateAgentBridgeTokenAction::run(User::query()->create([
        'name' => 'Token Owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]), 'Test client', ['capell.fake.write']);
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $created['token']->getKey(),
        name: 'Test client',
        scopes: ['capell.fake.write'],
    );

    InvokeAgentBridgeCapabilityPreviewAction::run(
        capabilityKey: 'capell.fake.write',
        payload: ['name' => 'Original'],
        client: $client,
        token: $created['token'],
    );
})->throws(AuthorizationException::class, 'Agent Bridge policy ability [preview fake capability] requires an authenticated user.');
