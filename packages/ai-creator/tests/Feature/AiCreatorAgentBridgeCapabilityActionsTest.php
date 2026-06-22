<?php

declare(strict_types=1);

use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AiCreator\Actions\StartAiCreatorSessionAction;
use Capell\AiCreator\AgentBridge\ApplyAiCreatorSessionCapabilityAction;
use Capell\AiCreator\AgentBridge\PreviewAiCreatorSessionCapabilityAction;
use Capell\AiCreator\AgentBridge\StartAiCreatorSessionCapabilityAction;
use Capell\AiCreator\Data\AiCreatorStartSessionData;
use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Capell\AiCreator\Tests\Fixtures\SiteScopedAgentBridgeUser;
use Illuminate\Validation\ValidationException;

it('keeps agent bridge preview calls non mutating', function (): void {
    $session = StartAiCreatorSessionAction::run(new AiCreatorStartSessionData(
        intent: 'Create a landing page with analytics reporting.',
        userId: 11,
    ));
    $user = new SiteScopedAgentBridgeUser(identifier: 11, assignedSiteIds: []);
    $payload = ['session_id' => (int) $session->getKey()];

    $previewCapability = ai_creator_capability('capell.ai-creator.sessions.preview_apply');
    $applyCapability = ai_creator_capability('capell.ai-creator.sessions.apply');

    (new PreviewAiCreatorSessionCapabilityAction)->preview(new CapabilityInvocationData($previewCapability, $payload, user: $user));
    $session->refresh();

    expect($session->status)->toBe(AiCreatorSessionStatus::Draft)
        ->and($session->preview_output)->toBeNull();

    (new PreviewAiCreatorSessionCapabilityAction)->execute(new CapabilityInvocationData($previewCapability, $payload, user: $user));
    $session->refresh();

    expect($session->status)->toBe(AiCreatorSessionStatus::Draft)
        ->and($session->preview_output)->toBeNull();

    (new ApplyAiCreatorSessionCapabilityAction)->preview(new CapabilityInvocationData($applyCapability, $payload, user: $user));
    $session->refresh();

    expect($session->status)->toBe(AiCreatorSessionStatus::Draft)
        ->and($session->preview_output)->toBeNull();
});

it('rejects agent bridge session access for non owners', function (): void {
    $session = StartAiCreatorSessionAction::run(new AiCreatorStartSessionData(
        intent: 'Create content.',
        userId: 11,
    ));
    $otherUser = new SiteScopedAgentBridgeUser(identifier: 22, assignedSiteIds: []);
    $capability = ai_creator_capability('capell.ai-creator.sessions.preview_apply');

    (new PreviewAiCreatorSessionCapabilityAction)->preview(new CapabilityInvocationData(
        capability: $capability,
        payload: ['session_id' => (int) $session->getKey()],
        user: $otherUser,
    ));
})->throws(ValidationException::class);

it('authorizes site scoped sessions against assigned sites', function (): void {
    $session = StartAiCreatorSessionAction::run(new AiCreatorStartSessionData(
        intent: 'Create site content.',
        siteId: 10,
        userId: 11,
    ));
    $capability = ai_creator_capability('capell.ai-creator.sessions.preview_apply');

    $allowedUser = new SiteScopedAgentBridgeUser(identifier: 22, assignedSiteIds: [10]);
    $forbiddenUser = new SiteScopedAgentBridgeUser(identifier: 33, assignedSiteIds: [20]);

    $result = (new PreviewAiCreatorSessionCapabilityAction)->preview(new CapabilityInvocationData(
        capability: $capability,
        payload: ['session_id' => (int) $session->getKey()],
        user: $allowedUser,
    ));

    expect($result->ok)->toBeTrue();

    (new PreviewAiCreatorSessionCapabilityAction)->preview(new CapabilityInvocationData(
        capability: $capability,
        payload: ['session_id' => (int) $session->getKey()],
        user: $forbiddenUser,
    ));
})->throws(ValidationException::class);

it('validates agent bridge start payloads and authorizes requested sites', function (): void {
    $capability = ai_creator_capability('capell.ai-creator.sessions.start');
    $allowedUser = new SiteScopedAgentBridgeUser(identifier: 22, assignedSiteIds: [10]);
    $forbiddenUser = new SiteScopedAgentBridgeUser(identifier: 33, assignedSiteIds: [20]);

    $result = (new StartAiCreatorSessionCapabilityAction)->execute(new CapabilityInvocationData(
        capability: $capability,
        payload: [
            'intent' => 'Create a campaign page.',
            'site_id' => 10,
        ],
        user: $allowedUser,
    ));

    expect($result->ok)->toBeTrue()
        ->and($result->data['sessionId'] ?? null)->toBeInt();

    (new StartAiCreatorSessionCapabilityAction)->preview(new CapabilityInvocationData(
        capability: $capability,
        payload: [
            'intent' => 'Create a campaign page.',
            'site_id' => 10,
        ],
        user: $forbiddenUser,
    ));
})->throws(ValidationException::class);

it('rejects malformed agent bridge payloads before defaulting values', function (): void {
    $capability = ai_creator_capability('capell.ai-creator.sessions.start');
    $user = new SiteScopedAgentBridgeUser(identifier: 22, assignedSiteIds: []);

    (new StartAiCreatorSessionCapabilityAction)->preview(new CapabilityInvocationData(
        capability: $capability,
        payload: [],
        user: $user,
    ));
})->throws(ValidationException::class);

function ai_creator_capability(string $key): CapabilityData
{
    return resolve(CapellAgentBridgeCapabilityRegistry::class)->get($key);
}
