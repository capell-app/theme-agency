<?php

declare(strict_types=1);

use Capell\AgentBridge\Data\AuthenticatedAgentBridgeClientData;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Health\AgentBridgeHealthCheck;
use Capell\AgentBridge\Tests\Fixtures\FakeCapabilityAction;

it('serializes capability definitions into agent payloads', function (): void {
    $capability = new CapabilityData(
        key: 'site.fake',
        name: 'Fake Site Capability',
        description: 'Exercises the bridge data contract.',
        scope: 'site:write',
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::Medium,
        actionClass: FakeCapabilityAction::class,
        requiredPackage: 'capell-pages',
        policyAbility: 'update',
        inputDataClass: CapabilityInvocationData::class,
        outputDataClass: CapabilityResultData::class,
        inputSchema: ['type' => 'object'],
        outputSchema: ['type' => 'object'],
        supportsPreview: false,
        requiresConfirmation: false,
        auditEvent: 'site.fake',
    );

    expect($capability->needsConfirmation())->toBeTrue()
        ->and($capability->toPayload())->toBe([
            'key' => 'site.fake',
            'name' => 'Fake Site Capability',
            'description' => 'Exercises the bridge data contract.',
            'scope' => 'site:write',
            'server' => 'site',
            'risk' => 'medium',
            'requiredPackage' => 'capell-pages',
            'policyAbility' => 'update',
            'inputDataClass' => CapabilityInvocationData::class,
            'outputDataClass' => CapabilityResultData::class,
            'inputSchema' => ['type' => 'object'],
            'outputSchema' => ['type' => 'object'],
            'supportsPreview' => false,
            'requiresConfirmation' => false,
            'auditEvent' => 'site.fake',
            'public' => false,
        ]);
});

it('checks client scopes and carries invocation context', function (): void {
    $capability = new CapabilityData(
        key: 'knowledge.read',
        name: 'Read Knowledge',
        description: 'Read package documentation.',
        scope: 'knowledge:read',
        server: CapabilityServerEnum::Knowledge,
        risk: CapabilityRiskEnum::Read,
        actionClass: FakeCapabilityAction::class,
        requiresConfirmation: false,
    );
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: 12,
        name: 'Codex',
        scopes: ['knowledge:read'],
    );
    $wildcardClient = new AuthenticatedAgentBridgeClientData(
        tokenId: 13,
        name: 'Admin Agent',
        scopes: ['*'],
    );
    $invocation = new CapabilityInvocationData(
        capability: $capability,
        payload: ['path' => 'overview.md'],
        client: $client,
        meta: ['request_id' => 'req-1'],
    );

    expect($capability->needsConfirmation())->toBeFalse()
        ->and($client->can('knowledge:read'))->toBeTrue()
        ->and($client->can('site:write'))->toBeFalse()
        ->and($wildcardClient->can('site:write'))->toBeTrue()
        ->and($invocation->payload)->toBe(['path' => 'overview.md'])
        ->and($invocation->meta)->toBe(['request_id' => 'req-1']);
});

it('serializes capability results for bridge tools', function (): void {
    $result = new CapabilityResultData(
        ok: false,
        message: 'Preview requires confirmation.',
        data: ['confirmation_required' => true],
        warnings: ['This operation changes content.'],
    );

    expect($result->toPayload())->toBe([
        'ok' => false,
        'message' => 'Preview requires confirmation.',
        'data' => ['confirmation_required' => true],
        'warnings' => ['This operation changes content.'],
    ]);
});

it('defines bridge enum visibility and health metadata', function (): void {
    expect(AgentBridgeHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(CapabilityRiskEnum::Read->requiresConfirmation())->toBeFalse()
        ->and(CapabilityRiskEnum::Low->requiresConfirmation())->toBeTrue()
        ->and(CapabilityRiskEnum::Destructive->requiresConfirmation())->toBeTrue()
        ->and(CapabilityServerEnum::Both->isVisibleOn(CapabilityServerEnum::Knowledge))->toBeTrue()
        ->and(CapabilityServerEnum::Both->isVisibleOn(CapabilityServerEnum::Site))->toBeTrue()
        ->and(CapabilityServerEnum::Knowledge->isVisibleOn(CapabilityServerEnum::Knowledge))->toBeTrue()
        ->and(CapabilityServerEnum::Knowledge->isVisibleOn(CapabilityServerEnum::Site))->toBeFalse();
});
