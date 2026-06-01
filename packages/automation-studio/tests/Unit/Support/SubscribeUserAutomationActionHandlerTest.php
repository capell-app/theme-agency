<?php

declare(strict_types=1);

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\Handlers\SubscribeUserAutomationActionHandler;

it('provides a native newsletter subscription handler', function (): void {
    expect(new SubscribeUserAutomationActionHandler)->toBeInstanceOf(AutomationActionHandler::class);
});

it('reports missing site ids before subscribing users', function (): void {
    $result = (new SubscribeUserAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['email' => 'person@example.test'],
        ),
        action: new AutomationRuleActionData(
            key: 'subscribe-user',
            type: AutomationActionType::SubscribeUser,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('A site id is required to subscribe a user.');
});

it('reports missing email addresses before subscribing users', function (): void {
    $result = (new SubscribeUserAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['site_id' => 1],
        ),
        action: new AutomationRuleActionData(
            key: 'subscribe-user',
            type: AutomationActionType::SubscribeUser,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('An email address is required to subscribe a user.');
});
