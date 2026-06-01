<?php

declare(strict_types=1);

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\Handlers\SendEmailAutomationActionHandler;

it('provides a native email studio automation handler', function (): void {
    expect(new SendEmailAutomationActionHandler)->toBeInstanceOf(AutomationActionHandler::class);
});

it('reports missing email template keys before invoking email studio', function (): void {
    $result = (new SendEmailAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['email' => 'person@example.test'],
        ),
        action: new AutomationRuleActionData(
            key: 'send-email',
            type: AutomationActionType::SendEmail,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('An Email Studio template key is required.');
});

it('reports missing email recipients before invoking email studio', function (): void {
    $result = (new SendEmailAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['site_id' => 1],
        ),
        action: new AutomationRuleActionData(
            key: 'send-email',
            type: AutomationActionType::SendEmail,
            settings: ['template_key' => 'welcome'],
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('At least one email recipient is required.');
});
