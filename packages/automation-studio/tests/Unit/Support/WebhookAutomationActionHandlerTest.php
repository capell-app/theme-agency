<?php

declare(strict_types=1);

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\Handlers\DispatchWebhookAutomationActionHandler;

it('provides a native webhook handler backed by public action destinations', function (): void {
    expect(new DispatchWebhookAutomationActionHandler)->toBeInstanceOf(AutomationActionHandler::class);
});

it('reports missing webhook public action keys without requiring a booted translator', function (): void {
    $result = (new DispatchWebhookAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['email' => 'person@example.test'],
        ),
        action: new AutomationRuleActionData(
            key: 'notify-webhook',
            type: AutomationActionType::Webhook,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('A Public Action webhook key is required.');
});
