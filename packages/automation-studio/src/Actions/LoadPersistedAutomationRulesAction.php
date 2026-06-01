<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class LoadPersistedAutomationRulesAction
{
    use AsAction;

    public function __construct(
        private readonly AutomationRuleRegistry $rules,
    ) {}

    /**
     * @return Collection<int, AutomationRuleData>
     */
    public function handle(?int $siteId = null): Collection
    {
        $rules = AutomationRule::query()
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
            ->map(fn (AutomationRule $rule): AutomationRuleData => $rule->toRuleData());

        $this->rules->registerMany($rules);

        return $rules;
    }
}
