<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\Admin\Support\SiteScope;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

final class ReplayAutomationRunAction
{
    use AsAction;

    public function __construct(
        private readonly AutomationActionRegistry $actions,
        private readonly PersistAutomationTriggerResultsAction $persistResults,
    ) {}

    /**
     * @return list<AutomationActionResultData>
     */
    public function handle(AutomationRun $run): array
    {
        $this->authorizeReplay($run);

        throw_unless($this->canReplay($run), RuntimeException::class, $this->notReplayableMessage());

        $rule = $this->ruleForRun($run);

        throw_unless($rule instanceof AutomationRule, RuntimeException::class, $this->missingRuleMessage());

        $action = $this->actionForRun($rule, $run);

        throw_unless($action instanceof AutomationRuleActionData, RuntimeException::class, $this->missingActionMessage());

        $rules = new AutomationRuleRegistry;
        $rules->register(new AutomationRuleData(
            key: $rule->key,
            name: $rule->name,
            triggerType: $run->trigger_type,
            actions: [$action],
            status: AutomationRuleStatus::Active,
            conditions: [],
        ));

        return (new DispatchAutomationTriggerAction($rules, $this->actions, $this->persistResults))->handle(
            event: new AutomationTriggerEventData(
                triggerType: $run->trigger_type,
                sourceType: (string) ($run->source_type ?? 'automation-run-replay'),
                sourceId: $run->source_id,
                payload: $run->payload ?? [],
                occurredAt: CarbonImmutable::now(),
            ),
            idempotencyKey: $this->replayIdempotencyKey($run),
            siteId: is_int($run->site_id) ? $run->site_id : null,
            attemptNumber: max(1, ($run->attempt_number ?? 1) + 1),
            maxAttempts: $run->max_attempts,
        );
    }

    public function canReplay(AutomationRun $run): bool
    {
        return in_array($run->status, [
            AutomationRunStatus::Failed,
            AutomationRunStatus::Pending,
            AutomationRunStatus::Skipped,
        ], true)
            && is_string($run->rule_key)
            && $run->rule_key !== ''
            && is_string($run->action_key)
            && $run->action_key !== '';
    }

    /**
     * Ensure the authenticated actor (if any) is allowed to replay this run's
     * site. When invoked from a system context (no authenticated actor, e.g. a
     * queued retry) replay proceeds, but an interactive actor scoped to other
     * sites is denied so replay cannot re-fire another site's automations.
     */
    private function authorizeReplay(AutomationRun $run): void
    {
        $actor = auth()->user();

        if (! $actor instanceof Authenticatable) {
            return;
        }

        if (SiteScope::isGlobalActor($actor)) {
            return;
        }

        $siteId = is_int($run->site_id) ? $run->site_id : null;

        throw_unless(
            $siteId !== null && $actor->getAssignedSiteIds()->contains($siteId),
            AuthorizationException::class,
            $this->replayDeniedMessage(),
        );
    }

    private function ruleForRun(AutomationRun $run): ?AutomationRule
    {
        if ($run->relationLoaded('rule') && $run->rule instanceof AutomationRule) {
            return $run->rule;
        }

        if ($run->automation_rule_id !== null) {
            /** @var AutomationRule|null $rule */
            $rule = AutomationRule::query()->whereKey($run->automation_rule_id)->first();

            if ($rule instanceof AutomationRule) {
                return $rule;
            }
        }

        /** @var AutomationRule|null $rule */
        $rule = AutomationRule::query()->where('key', $run->rule_key)->first();

        return $rule;
    }

    private function actionForRun(AutomationRule $rule, AutomationRun $run): ?AutomationRuleActionData
    {
        return collect($rule->toRuleData()->actions)
            ->first(static fn (AutomationRuleActionData $action): bool => $action->key === $run->action_key);
    }

    private function replayIdempotencyKey(AutomationRun $run): string
    {
        return implode(':', [
            'replay',
            $run->getKey(),
            max(1, ($run->attempt_number ?? 1) + 1),
        ]);
    }

    private function notReplayableMessage(): string
    {
        return (string) __('capell-automation-studio::generic.replay.not_replayable');
    }

    private function replayDeniedMessage(): string
    {
        return (string) __('capell-automation-studio::generic.replay.denied');
    }

    private function missingRuleMessage(): string
    {
        return (string) __('capell-automation-studio::generic.replay.rule_missing');
    }

    private function missingActionMessage(): string
    {
        return (string) __('capell-automation-studio::generic.replay.action_missing');
    }
}
