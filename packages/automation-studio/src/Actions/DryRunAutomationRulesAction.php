<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationRuleDryRunResultData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class DryRunAutomationRulesAction
{
    use AsAction;

    /**
     * @return list<AutomationRuleDryRunResultData>
     */
    public function handle(AutomationTriggerEventData $event, ?int $siteId = null): array
    {
        $registry = new AutomationRuleRegistry;
        $registry->registerMany($this->rules($siteId));

        return collect($registry->matching($event))
            ->map(static fn (AutomationRuleData $rule): AutomationRuleDryRunResultData => new AutomationRuleDryRunResultData(
                ruleKey: $rule->key,
                ruleName: $rule->name,
                triggerType: $rule->triggerType,
                actionKeys: collect($rule->actions)
                    ->pluck('key')
                    ->filter(static fn (mixed $key): bool => is_string($key) && $key !== '')
                    ->values()
                    ->all(),
                actionTypes: collect($rule->actions)
                    ->map(static fn (mixed $action): ?string => $action instanceof AutomationRuleActionData ? $action->type->value : null)
                    ->filter(static fn (?string $type): bool => $type !== null)
                    ->values()
                    ->all(),
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<AutomationRuleData>
     */
    private function rules(?int $siteId): array
    {
        return AutomationRule::query()
            ->where('status', AutomationRuleStatus::Active)
            ->when($siteId !== null, function (Builder $query) use ($siteId): void {
                $query->where(function (Builder $siteQuery) use ($siteId): void {
                    $siteQuery
                        ->whereNull('site_id')
                        ->orWhere('site_id', $siteId);
                });
            })
            ->orderBy('id')
            ->get()
            ->map(static fn (AutomationRule $rule): AutomationRuleData => $rule->toRuleData())
            ->values()
            ->all();
    }
}
