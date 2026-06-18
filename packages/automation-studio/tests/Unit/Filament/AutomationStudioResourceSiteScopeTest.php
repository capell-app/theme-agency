<?php

declare(strict_types=1);

use Capell\AutomationStudio\Actions\PersistAutomationTriggerResultsAction;
use Capell\AutomationStudio\Actions\ReplayAutomationRunAction;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Filament\Resources\AutomationRules\AutomationRuleResource;
use Capell\AutomationStudio\Filament\Resources\AutomationRuns\AutomationRunResource;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Policies\AutomationRunPolicy;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Lightweight actor used to exercise the SiteScope and policy paths without the
 * host application's Filament Shield + permission tables. Mirrors the
 * public-actions package fixture so behaviour matches the sibling resources.
 */
final class AutomationStudioSiteScopedTestUser extends User
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    /**
     * @param  list<int>  $assignedSiteIds
     */
    public function __construct(
        private readonly array $assignedSiteIds = [],
        private readonly bool $superAdmin = false,
    ) {
        parent::__construct();
    }

    public function checkPermissionTo(string $permission): bool
    {
        return true;
    }

    public function hasRole(string $role): bool
    {
        return $this->superAdmin && $role === config('capell.roles.super_admin', 'super_admin');
    }

    /**
     * @return Collection<int, int>
     */
    public function getAssignedSiteIds(): Collection
    {
        return collect($this->assignedSiteIds);
    }
}

function automationStudioRuleForSite(int $siteId, string $key): AutomationRule
{
    /** @var AutomationRule $rule */
    $rule = AutomationRule::query()->create([
        'site_id' => $siteId,
        'key' => $key,
        'name' => 'Rule ' . $key,
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'status' => AutomationRuleStatus::Active,
        'conditions' => [],
        'actions' => [
            [
                'key' => 'send-thank-you',
                'type' => AutomationActionType::SendEmail->value,
            ],
        ],
    ]);

    return $rule;
}

function automationStudioRunForRule(AutomationRule $rule): AutomationRun
{
    /** @var AutomationRun $run */
    $run = AutomationRun::query()->create([
        'automation_rule_id' => $rule->getKey(),
        'site_id' => $rule->site_id,
        'rule_key' => $rule->key,
        'action_key' => 'send-thank-you',
        'trigger_type' => AutomationTriggerType::FormSubmitted,
        'action_type' => AutomationActionType::SendEmail,
        'source_type' => 'form-builder.form',
        'source_id' => 'contact',
        'idempotency_key' => 'original:' . $rule->key,
        'attempt_number' => 1,
        'max_attempts' => 3,
        'status' => AutomationRunStatus::Failed,
        'message' => 'Original result',
        'payload' => ['email' => 'person@example.test'],
        'context' => [],
        'started_at' => now(),
        'finished_at' => now(),
    ]);

    return $run;
}

it('scopes automation rule and run resources to the actor assigned sites', function (): void {
    $siteA = 1;
    $siteB = 2;

    $ruleA = automationStudioRuleForSite($siteA, 'site-a-rule');
    $ruleB = automationStudioRuleForSite($siteB, 'site-b-rule');
    $runA = automationStudioRunForRule($ruleA);
    $runB = automationStudioRunForRule($ruleB);

    $actor = new AutomationStudioSiteScopedTestUser(assignedSiteIds: [$siteA]);
    $this->actingAs($actor);

    $visibleRuleIds = AutomationRuleResource::getEloquentQuery()->pluck('id')->all();
    $visibleRunIds = AutomationRunResource::getEloquentQuery()->pluck('id')->all();

    expect($visibleRuleIds)->toBe([$ruleA->getKey()])
        ->and($visibleRuleIds)->not->toContain($ruleB->getKey())
        ->and($visibleRunIds)->toBe([$runA->getKey()])
        ->and($visibleRunIds)->not->toContain($runB->getKey());
});

it('denies replay for an automation run outside the actor assigned sites', function (): void {
    Gate::policy(AutomationRun::class, AutomationRunPolicy::class);

    $foreignRule = automationStudioRuleForSite(2, 'site-b-rule');
    $foreignRun = automationStudioRunForRule($foreignRule);

    $actor = new AutomationStudioSiteScopedTestUser(assignedSiteIds: [1]);
    $this->actingAs($actor);

    // Resource action gate (mirrors ->authorize('update')) denies the replay button.
    expect(Gate::forUser($actor)->allows('update', $foreignRun))->toBeFalse();

    // Defence in depth: invoking the action directly is also denied.
    $action = new ReplayAutomationRunAction(
        new AutomationActionRegistry,
        new PersistAutomationTriggerResultsAction,
    );

    expect(fn (): array => $action->handle($foreignRun))->toThrow(AuthorizationException::class);

    // No replay attempt was persisted.
    expect(AutomationRun::query()->count())->toBe(1);
});
