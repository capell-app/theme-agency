<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\DryRunAutomationRulesAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;

it('previews matching persisted rules without executing actions or recording runs', function (): void {
    AutomationRule::query()->create([
        'key' => 'contact-lead',
        'name' => 'Contact lead',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => ['form_handle' => 'contact'],
        'actions' => [
            [
                'key' => 'send-thank-you',
                'type' => AutomationActionType::SendEmail->value,
            ],
            [
                'key' => 'tag-lead',
                'type' => AutomationActionType::TagContact->value,
            ],
        ],
    ]);
    AutomationRule::query()->create([
        'key' => 'paused-contact-lead',
        'name' => 'Paused contact lead',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Paused,
        'conditions' => ['form_handle' => 'contact'],
        'actions' => [
            [
                'key' => 'paused-send',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);
    AutomationRule::query()->create([
        'key' => 'support-lead',
        'name' => 'Support lead',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => ['form_handle' => 'support'],
        'actions' => [
            [
                'key' => 'support-send',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);

    $results = DryRunAutomationRulesAction::run(new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::FormSubmitted,
        sourceType: 'automation-studio.dry-run',
        payload: ['form_handle' => 'contact'],
    ));

    expect($results)->toHaveCount(1)
        ->and($results[0]->ruleKey)->toBe('contact-lead')
        ->and($results[0]->actionKeys)->toBe(['send-thank-you', 'tag-lead'])
        ->and($results[0]->actionTypes)->toBe(['send_email', 'tag_contact'])
        ->and($results[0]->actionCount())->toBe(2)
        ->and(AutomationRun::query()->count())->toBe(0);
});

it('scopes dry-run previews to global and selected site rules', function (): void {
    AutomationRule::query()->create([
        'site_id' => null,
        'key' => 'global-rule',
        'name' => 'Global rule',
        'trigger_type' => AutomationTriggerType::CampaignConverted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'global-webhook',
                'type' => AutomationActionType::Webhook->value,
            ],
        ],
    ]);
    AutomationRule::query()->create([
        'site_id' => 10,
        'key' => 'site-rule',
        'name' => 'Site rule',
        'trigger_type' => AutomationTriggerType::CampaignConverted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'site-webhook',
                'type' => AutomationActionType::Webhook->value,
            ],
        ],
    ]);
    AutomationRule::query()->create([
        'site_id' => 20,
        'key' => 'other-site-rule',
        'name' => 'Other site rule',
        'trigger_type' => AutomationTriggerType::CampaignConverted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'other-site-webhook',
                'type' => AutomationActionType::Webhook->value,
            ],
        ],
    ]);

    $results = DryRunAutomationRulesAction::run(new AutomationTriggerEventData(
        triggerType: AutomationTriggerType::CampaignConverted,
        sourceType: 'automation-studio.dry-run',
    ), siteId: 10);

    expect(collect($results)->pluck('ruleKey')->all())->toBe([
        'global-rule',
        'site-rule',
    ]);
});
