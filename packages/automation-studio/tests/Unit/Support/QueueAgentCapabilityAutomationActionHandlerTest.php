<?php

declare(strict_types=1);

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
