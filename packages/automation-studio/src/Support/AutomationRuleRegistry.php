<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support;

use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
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

        foreach ($rule->conditions as $payloadKey => $expectedValue) {
            if (! is_string($payloadKey)) {
                return false;
            }

            if (Arr::get($event->payload, $payloadKey) !== $expectedValue) {
                return false;
            }
        }

        return true;
    }
}
