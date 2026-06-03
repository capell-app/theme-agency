<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\RecordAutomationRunAction;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Models\AutomationRun;

it('updates existing runs when an idempotency key is reused', function (): void {
    $event = new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'form-builder.form',
        sourceId: 'contact',
        payload: ['email' => 'person@example.test'],
    );
    $action = new AutomationRuleActionData(
        key: 'notify',
        type: AutomationActionType::SendEmail,
    );

    RecordAutomationRunAction::run(
        event: $event,
        action: $action,
        result: new AutomationActionResultData(success: false, message: 'First attempt failed'),
        idempotencyKey: 'trigger-1:rule:notify',
        attemptNumber: 1,
        maxAttempts: 3,
    );
    RecordAutomationRunAction::run(
        event: $event,
        action: $action,
        result: new AutomationActionResultData(success: true, message: 'Second attempt succeeded'),
        idempotencyKey: 'trigger-1:rule:notify',
        attemptNumber: 2,
        maxAttempts: 3,
    );

    $run = AutomationRun::query()->sole();

    expect($run->status)->toBe(AutomationRunStatus::Succeeded)
        ->and($run->message)->toBe('Second attempt succeeded')
        ->and($run->attempt_number)->toBe(2)
        ->and($run->max_attempts)->toBe(3);
});
