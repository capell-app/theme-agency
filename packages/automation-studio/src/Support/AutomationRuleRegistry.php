<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support;

use Capell\AutomationStudio\Data\AutomationRuleConditionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationRuleConditionOperator;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Illuminate\Support\Arr;

final class AutomationRuleRegistry
{
    /** @var array<string, AutomationRuleData> */
    private array $rules = [];

    public function register(AutomationRuleData $rule): void
    {
        $this->rules[$rule->key] = $rule;
    }

    /**
     * @param  iterable<AutomationRuleData>  $rules
     */
    public function registerMany(iterable $rules): void
    {
        foreach ($rules as $rule) {
            $this->register($rule);
        }
    }

    public function get(string $key): ?AutomationRuleData
    {
        return $this->rules[$key] ?? null;
    }

    /**
     * @return list<AutomationRuleData>
     */
    public function matching(AutomationTriggerEventData $event): array
    {
        return array_values(collect($this->rules)
            ->filter(fn (AutomationRuleData $rule): bool => $this->matches($rule, $event))
            ->values()
            ->all());
    }

    /**
     * @return array<string, AutomationRuleData>
     */
    public function all(): array
    {
        return $this->rules;
    }

    private function matches(AutomationRuleData $rule, AutomationTriggerEventData $event): bool
    {
        if ($rule->status !== AutomationRuleStatus::Active) {
            return false;
        }

        if ($rule->triggerType !== $event->triggerType) {
            return false;
        }

        foreach ($this->conditions($rule->conditions) as $condition) {
            if (! $this->conditionMatches($condition, $event->payload)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int|string, mixed>  $conditions
     * @return list<AutomationRuleConditionData>
     */
    private function conditions(array $conditions): array
    {
        if ($this->isStructuredConditionList($conditions)) {
            return array_values(collect($conditions)
                ->map(static fn (mixed $condition): ?AutomationRuleConditionData => is_array($condition)
                    ? AutomationRuleConditionData::fromArray($condition)
                    : null)
                ->filter(static fn (?AutomationRuleConditionData $condition): bool => $condition instanceof AutomationRuleConditionData)
                ->all());
        }

        return array_values(collect($conditions)
            ->map(static fn (mixed $expectedValue, int|string $payloadKey): ?AutomationRuleConditionData => is_string($payloadKey)
                ? new AutomationRuleConditionData(
                    field: $payloadKey,
                    operator: AutomationRuleConditionOperator::Equals,
                    value: $expectedValue,
                )
                : null)
            ->filter(static fn (?AutomationRuleConditionData $condition): bool => $condition instanceof AutomationRuleConditionData)
            ->all());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function conditionMatches(AutomationRuleConditionData $condition, array $payload): bool
    {
        $actualValue = Arr::get($payload, $condition->field);

        return match ($condition->operator) {
            AutomationRuleConditionOperator::Equals => $actualValue === $condition->value,
            AutomationRuleConditionOperator::NotEquals => $actualValue !== $condition->value,
            AutomationRuleConditionOperator::Filled => ! in_array($actualValue, [null, ''], true),
            AutomationRuleConditionOperator::Blank => in_array($actualValue, [null, ''], true),
        };
    }

    /**
     * @param  array<int|string, mixed>  $conditions
     */
    private function isStructuredConditionList(array $conditions): bool
    {
        if ($conditions === []) {
            return false;
        }

        return array_is_list($conditions)
            && collect($conditions)->every(static fn (mixed $condition): bool => is_array($condition) && array_key_exists('field', $condition));
    }
}
