<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;

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
