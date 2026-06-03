<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Actions\PersistAutomationTriggerResultsAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Psr\Log\NullLogger;

it('dispatches active matching rules to registered action handlers', function (): void {
    $actions = new AutomationActionRegistry;
    $rules = new AutomationRuleRegistry;

    $actions->registerHandler(AutomationActionType::SendEmail, new class implements AutomationActionHandler
    {
        public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
        {
            return new AutomationActionResultData(
                success: true,
                message: (string) $event->payload['email'],
                context: ['handled_action' => $action->key],
            );
        }
    });

    $rules->register(new AutomationRuleData(
        key: 'contact-form-thank-you',
        name: 'Contact form thank you',
        triggerType: AutomationTriggerType::FormSubmitted,
        actions: [
            new AutomationRuleActionData(
                key: 'send-thank-you',
                type: AutomationActionType::SendEmail,
            ),
        ],
        conditions: ['form_handle' => 'contact'],
    ));

    $rules->register(new AutomationRuleData(
        key: 'paused-rule',
        name: 'Paused rule',
        triggerType: AutomationTriggerType::FormSubmitted,
        actions: [
            new AutomationRuleActionData(
                key: 'paused-action',
                type: AutomationActionType::SendEmail,
            ),
        ],
        status: AutomationRuleStatus::Paused,
        conditions: ['form_handle' => 'contact'],
    ));

    $results = (new DispatchAutomationTriggerAction($rules, $actions))->handle(new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'form-builder.form',
        sourceId: 'contact',
        payload: [
            'form_handle' => 'contact',
            'email' => 'person@example.test',
        ],
    ));

    expect($results)->toHaveCount(1)
        ->and($results[0]->success)->toBeTrue()
        ->and($results[0]->message)->toBe('person@example.test')
        ->and($results[0]->context)->toMatchArray([
            'rule_key' => 'contact-form-thank-you',
            'action_key' => 'send-thank-you',
            'handled_action' => 'send-thank-you',
        ]);
});

it('sanitizes failed handler results and keeps raw exception details out of run context', function (): void {
    $actions = new AutomationActionRegistry;
    $rules = new AutomationRuleRegistry;

    $actions->registerHandler(AutomationActionType::SendEmail, new class implements AutomationActionHandler
    {
        public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
        {
            throw new RuntimeException('SMTP password secret leaked from provider');
        }
    });

    $rules->register(new AutomationRuleData(
        key: 'failed-notification',
        name: 'Failed notification',
        triggerType: AutomationTriggerType::FormSubmitted,
        actions: [
            new AutomationRuleActionData(
                key: 'notify',
                type: AutomationActionType::SendEmail,
            ),
        ],
    ));

    $results = (new DispatchAutomationTriggerAction($rules, $actions, logger: new NullLogger))->handle(new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'form-builder.form',
    ));

    expect($results)->toHaveCount(1)
        ->and($results[0]->success)->toBeFalse()
        ->and($results[0]->message)->toContain('failed')
        ->and($results[0]->message)->not->toContain('SMTP password')
        ->and($results[0]->context)->toMatchArray([
            'rule_key' => 'failed-notification',
            'action_key' => 'notify',
            'error' => 'handler_failed',
        ])
        ->and($results[0]->context)->not->toHaveKey('error_type');
});

it('persists synchronous dispatch results when a persister is available', function (): void {
    $actions = new AutomationActionRegistry;
    $rules = new AutomationRuleRegistry;
    $rule = AutomationRule::query()->create([
        'key' => 'sync-notification',
        'name' => 'Sync notification',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'notify',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);

    $actions->registerHandler(AutomationActionType::SendEmail, new class implements AutomationActionHandler
    {
        public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
        {
            return new AutomationActionResultData(success: true, message: 'Sent');
        }
    });
    $rules->register($rule->toRuleData());

    (new DispatchAutomationTriggerAction($rules, $actions, new PersistAutomationTriggerResultsAction))->handle(new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'form-builder.form',
        sourceId: 'contact',
        payload: ['email' => 'person@example.test'],
    ));

    $run = AutomationRun::query()->sole();

    expect($run->automation_rule_id)->toBe($rule->getKey())
        ->and($run->rule_key)->toBe('sync-notification')
        ->and($run->action_key)->toBe('notify')
        ->and($run->status)->toBe(AutomationRunStatus::Succeeded)
        ->and($run->idempotency_key)->toBeNull();
});

it('reports missing handlers without aborting trigger dispatch', function (): void {
    $actions = new AutomationActionRegistry;
    $rules = new AutomationRuleRegistry;

    $rules->register(new AutomationRuleData(
        key: 'tag-new-lead',
        name: 'Tag new lead',
        triggerType: AutomationTriggerType::FormSubmitted,
        actions: [
            new AutomationRuleActionData(
                key: 'tag-lead',
                type: AutomationActionType::TagContact,
            ),
        ],
    ));

    $results = (new DispatchAutomationTriggerAction($rules, $actions))->handle(new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'form-builder.form',
    ));

    expect($results)->toHaveCount(1)
        ->and($results[0]->success)->toBeFalse()
        ->and($results[0]->context)->toMatchArray([
            'rule_key' => 'tag-new-lead',
            'action_key' => 'tag-lead',
        ]);
});
