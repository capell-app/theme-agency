<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Actions\LoadPersistedAutomationRulesAction;
use Capell\AutomationStudio\Actions\PersistAutomationTriggerResultsAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Jobs\DispatchQueuedAutomationTriggerJob;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;

it('loads persisted rules, dispatches handlers, and persists queued run results', function (): void {
    AutomationRule::query()->create([
        'key' => 'queued-form-rule',
        'name' => 'Queued form rule',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => ['form_handle' => 'contact'],
        'actions' => [
            [
                'key' => 'notify',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);

    $rules = new AutomationRuleRegistry;
    $actions = new AutomationActionRegistry;
    $actions->registerHandler(AutomationActionType::SendEmail, new class implements AutomationActionHandler
    {
        public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
        {
            return new AutomationActionResultData(
                success: true,
                message: 'Queued handler ran',
                context: ['email' => $event->payload['email']],
            );
        }
    });

    $job = new DispatchQueuedAutomationTriggerJob(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            sourceId: 'contact',
            payload: [
                'form_handle' => 'contact',
                'email' => 'person@example.test',
            ],
        ),
        idempotencyKey: 'queued-trigger-1',
    );

    $job->handle(
        new LoadPersistedAutomationRulesAction($rules),
        new DispatchAutomationTriggerAction($rules, $actions, new PersistAutomationTriggerResultsAction),
    );

    $run = AutomationRun::query()->sole();

    expect($run->rule_key)->toBe('queued-form-rule')
        ->and($run->action_key)->toBe('notify')
        ->and($run->idempotency_key)->toBe('queued-trigger-1:queued-form-rule:notify')
        ->and($run->status)->toBe(AutomationRunStatus::Succeeded)
        ->and($run->message)->toBe('Queued handler ran')
        ->and($run->context)->toMatchArray([
            'email' => 'person@example.test',
            'rule_key' => 'queued-form-rule',
            'action_key' => 'notify',
        ]);
});
