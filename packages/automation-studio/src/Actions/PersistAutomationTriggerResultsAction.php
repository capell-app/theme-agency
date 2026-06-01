<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Models\AutomationRule;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class PersistAutomationTriggerResultsAction
{
    use AsAction;

    /**
     * @param  list<AutomationActionResultData>  $results
     */
    public function handle(
        AutomationTriggerEventData $event,
        array $results,
        string $idempotencyKey,
        ?int $siteId = null,
        int $attemptNumber = 1,
        ?int $maxAttempts = null,
    ): void {
        foreach ($results as $result) {
            $rule = $this->ruleFromResult($result);
            $action = $rule === null ? null : $this->actionFromResult($rule, $result);

            RecordAutomationRunAction::run(
                event: $event,
                rule: $rule,
                action: $action,
                result: $result,
                siteId: $siteId,
                idempotencyKey: $this->resultIdempotencyKey($idempotencyKey, $result),
                attemptNumber: $attemptNumber,
                maxAttempts: $maxAttempts,
            );
        }
    }

    private function ruleFromResult(AutomationActionResultData $result): ?AutomationRule
    {
        $ruleKey = $result->context['rule_key'] ?? null;

        if (! is_string($ruleKey) || $ruleKey === '') {
            return null;
        }

        /** @var AutomationRule|null $rule */
        $rule = AutomationRule::query()->where('key', $ruleKey)->first();

        return $rule;
    }

    private function actionFromResult(AutomationRule $rule, AutomationActionResultData $result): ?AutomationRuleActionData
    {
        $actionKey = $result->context['action_key'] ?? null;

        if (! is_string($actionKey) || $actionKey === '') {
            return null;
        }

        return Collection::make($rule->toRuleData()->actions)
            ->first(static fn (AutomationRuleActionData $action): bool => $action->key === $actionKey);
    }

    private function resultIdempotencyKey(string $idempotencyKey, AutomationActionResultData $result): string
    {
        $ruleKey = is_string($result->context['rule_key'] ?? null) ? $result->context['rule_key'] : 'unknown-rule';
        $actionKey = is_string($result->context['action_key'] ?? null) ? $result->context['action_key'] : 'unknown-action';

        return implode(':', [$idempotencyKey, $ruleKey, $actionKey]);
    }
}
