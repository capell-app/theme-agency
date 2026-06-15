<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\MessageRole;
use Capell\LiveChat\Integrations\AgentBridge\LiveChatAgentBridgeCapabilityProvider;
use Capell\LiveChat\Models\LiveChatConversation;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-15 10:00:00', 'Europe/London'));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

function live_chat_agent_bridge_registry(): CapellAgentBridgeCapabilityRegistry
{
    $registry = new CapellAgentBridgeCapabilityRegistry;

    (new LiveChatAgentBridgeCapabilityProvider)->registerCapabilities($registry);

    return $registry;
}

function live_chat_agent_bridge_action(CapabilityData $capability): CapellAgentBridgeCapabilityAction
{
    $action = app($capability->actionClass);

    throw_unless($action instanceof CapellAgentBridgeCapabilityAction, RuntimeException::class, 'Expected a live chat Agent Bridge capability action.');

    return $action;
}

it('registers live chat Agent Bridge capabilities with confirmation on mutating actions', function (): void {
    $registry = live_chat_agent_bridge_registry();
    $capabilityKeys = $registry->all()->pluck('key')->values()->all();
    $escalate = $registry->get('capell.live-chat.escalate');
    $close = $registry->get('capell.live-chat.close');

    expect($capabilityKeys)->toBe([
        'capell.live-chat.conversations.list',
        'capell.live-chat.conversations.inspect',
        'capell.live-chat.summary.preview',
        'capell.live-chat.reply.preview',
        'capell.live-chat.escalation.preview',
        'capell.live-chat.escalate',
        'capell.live-chat.close',
    ])
        ->and($escalate->risk)->toBe(CapabilityRiskEnum::High)
        ->and($escalate->needsConfirmation())->toBeTrue()
        ->and($escalate->auditEvent)->toBe('capell_agent-bridge.live-chat.escalated')
        ->and($close->needsConfirmation())->toBeTrue()
        ->and($close->auditEvent)->toBe('capell_agent-bridge.live-chat.closed');
});

it('previews summary and reply capabilities without mutating conversation metadata', function (): void {
    $registry = live_chat_agent_bridge_registry();
    $installation = $this->createLiveChatInstallation();
    $conversation = LiveChatConversation::query()->create([
        'site_id' => $installation->site_id,
        'installation_id' => $installation->id,
        'visitor_name' => 'Bridge Visitor',
        'metadata' => ['topic' => 'agent-bridge-preview'],
    ]);
    $conversation->messages()->create([
        'role' => MessageRole::Visitor,
        'body' => 'Can a human help with onboarding?',
    ]);
    $beforeMetadata = $conversation->metadata;

    foreach (['capell.live-chat.summary.preview', 'capell.live-chat.reply.preview'] as $capabilityKey) {
        $capability = $registry->get($capabilityKey);
        $result = live_chat_agent_bridge_action($capability)->preview(new CapabilityInvocationData(
            capability: $capability,
            payload: ['conversation_id' => $conversation->id],
        ));

        expect($result->ok)->toBeTrue()
            ->and($result->data)->not->toBe([]);
    }

    expect($conversation->refresh()->metadata)->toBe($beforeMetadata);
});

it('executes confirmed escalation and close actions against only the requested conversation', function (): void {
    $registry = live_chat_agent_bridge_registry();
    $installation = $this->createLiveChatInstallation();
    $targetConversation = LiveChatConversation::query()->create([
        'site_id' => $installation->site_id,
        'installation_id' => $installation->id,
    ]);
    $otherConversation = LiveChatConversation::query()->create([
        'site_id' => $installation->site_id,
        'installation_id' => $installation->id,
    ]);
    $targetConversation->messages()->create([
        'role' => MessageRole::Visitor,
        'body' => 'I need a person please.',
    ]);
    $otherConversation->messages()->create([
        'role' => MessageRole::Visitor,
        'body' => 'This chat should stay open.',
    ]);

    $escalate = $registry->get('capell.live-chat.escalate');
    $escalation = live_chat_agent_bridge_action($escalate)->execute(new CapabilityInvocationData(
        capability: $escalate,
        payload: [
            'conversation_id' => $targetConversation->id,
            'note' => 'Agent Bridge confirmed handoff.',
        ],
    ));

    expect($escalation->ok)->toBeTrue()
        ->and($targetConversation->refresh()->status)->toBe(ConversationStatus::WaitingForHuman)
        ->and($targetConversation->messages()->latest('id')->firstOrFail()->body)->toBe('Agent Bridge confirmed handoff.')
        ->and($otherConversation->refresh()->status)->toBe(ConversationStatus::Active);

    $close = $registry->get('capell.live-chat.close');
    $closed = live_chat_agent_bridge_action($close)->execute(new CapabilityInvocationData(
        capability: $close,
        payload: ['conversation_id' => $targetConversation->id],
    ));

    expect($closed->ok)->toBeTrue()
        ->and($targetConversation->refresh()->status)->toBe(ConversationStatus::Closed)
        ->and($otherConversation->refresh()->status)->toBe(ConversationStatus::Active);
});
