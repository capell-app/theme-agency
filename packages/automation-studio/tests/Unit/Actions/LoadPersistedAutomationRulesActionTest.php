<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\LoadPersistedAutomationRulesAction;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Illuminate\Support\Facades\DB;

it('loads active global and matching site rules into the registry', function (): void {
    DB::table('sites')->insert([
        ['id' => 10],
        ['id' => 11],
    ]);

    AutomationRule::query()->create([
        'key' => 'global-form-rule',
        'name' => 'Global form rule',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'global-action',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);
    AutomationRule::query()->create([
        'site_id' => 10,
        'key' => 'site-form-rule',
        'name' => 'Site form rule',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'site-action',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);
    AutomationRule::query()->create([
        'site_id' => 11,
        'key' => 'other-site-rule',
        'name' => 'Other site rule',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'other-site-action',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);
    AutomationRule::query()->create([
        'key' => 'paused-rule',
        'name' => 'Paused rule',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Paused,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'paused-action',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);

    $registry = new AutomationRuleRegistry;
    $loadedRules = (new LoadPersistedAutomationRulesAction($registry))->handle(10);

    expect($loadedRules->pluck('key')->all())->toBe([
        'global-form-rule',
        'site-form-rule',
    ])
        ->and(array_keys($registry->all()))->toBe([
            'global-form-rule',
            'site-form-rule',
        ]);
});
