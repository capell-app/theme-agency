<?php

declare(strict_types=1);

use Capell\AgentBridge\Actions\InvokeAgentBridgeCapabilityPreviewAction;
use Capell\AgentBridge\Data\AuthenticatedAgentBridgeClientData;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\Handlers\QueueAgentCapabilityAutomationActionHandler;

it('provides a native agent bridge capability handler', function (): void {
    expect(new QueueAgentCapabilityAutomationActionHandler)->toBeInstanceOf(AutomationActionHandler::class);
});

it('reports missing agent bridge capability keys before invoking agent bridge', function (): void {
    $result = (new QueueAgentCapabilityAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['site_id' => 1],
        ),
        action: new AutomationRuleActionData(
            key: 'queue-agent-capability',
            type: AutomationActionType::QueueAgentCapability,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('An Agent Bridge capability key is required.');
});

it('queues agent bridge capabilities with merged payload and scoped automation clients', function (): void {
    $invocation = new class
    {
        public ?string $capabilityKey = null;

        /** @var array<string, mixed> */
        public array $payload = [];

        public ?AuthenticatedAgentBridgeClientData $client = null;
    };

    app()->instance(InvokeAgentBridgeCapabilityPreviewAction::class, new class($invocation)
    {
        public function __construct(private readonly object $invocation) {}

        public function handle(
            string $capabilityKey,
            array $payload,
            ?AuthenticatedAgentBridgeClientData $client = null,
            mixed $token = null,
            mixed $user = null,
        ): array {
            $this->invocation->capabilityKey = $capabilityKey;
            $this->invocation->payload = $payload;
            $this->invocation->client = $client;

            return [
                'mode' => 'preview',
                'capability' => $capabilityKey,
                'payload' => $payload,
            ];
        }
    });

    $result = (new QueueAgentCapabilityAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.submission',
            sourceId: '99',
            payload: [
                'site_id' => 12,
                'email' => 'person@example.test',
                'priority' => 'normal',
            ],
        ),
        action: new AutomationRuleActionData(
            key: 'queue-agent-capability',
            type: AutomationActionType::QueueAgentCapability,
            settings: [
                'capability' => 'site.inspect',
                'payload' => [
                    'priority' => 'high',
                    'requested_by' => 'automation',
                ],
                'client_scopes' => [' site:read ', '', 'site:write'],
            ],
        ),
    );

    expect($result->success)->toBeTrue()
        ->and($result->message)->toBe('preview')
        ->and($result->context['agent_bridge']['capability'])->toBe('site.inspect')
        ->and($invocation->capabilityKey)->toBe('site.inspect')
        ->and($invocation->payload)->toMatchArray([
            'site_id' => 12,
            'email' => 'person@example.test',
            'priority' => 'high',
            'requested_by' => 'automation',
            'automation' => [
                'trigger_type' => 'form_submitted',
                'source_type' => 'form-builder.submission',
                'source_id' => '99',
                'action_key' => 'queue-agent-capability',
            ],
        ])
        ->and($invocation->client)->toBeInstanceOf(AuthenticatedAgentBridgeClientData::class)
        ->and($invocation->client?->name)->toBe('Automation Studio')
        ->and($invocation->client?->scopes)->toBe(['site:read', 'site:write']);
});

it('returns agent bridge invocation failures as automation action failures', function (): void {
    app()->instance(InvokeAgentBridgeCapabilityPreviewAction::class, new class
    {
        public function handle(
            string $capabilityKey,
            array $payload,
            ?AuthenticatedAgentBridgeClientData $client = null,
            mixed $token = null,
            mixed $user = null,
        ): array {
            throw new RuntimeException('Agent Bridge rejected the capability.');
        }
    });

    $result = (new QueueAgentCapabilityAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.submission',
            payload: ['site_id' => 12],
        ),
        action: new AutomationRuleActionData(
            key: 'queue-agent-capability',
            type: AutomationActionType::QueueAgentCapability,
            settings: [
                'capability_key' => 'site.inspect',
                'client_scopes' => 'ignored',
            ],
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('Agent Bridge rejected the capability.')
        ->and($result->context['error_type'])->toBe(RuntimeException::class);
});
