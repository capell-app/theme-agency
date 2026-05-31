<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Data\ExperimentContextData;
use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentAudienceRule;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class EvaluateAudienceRulesAction
{
    use AsAction;

    public function handle(Experiment $experiment, ExperimentContextData $context): bool
    {
        /** @var Collection<int, ExperimentAudienceRule> $rules */
        $rules = $experiment
            ->audienceRules()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($rules->isEmpty()) {
            return true;
        }

        $optionalRules = $rules->where('is_required', false);
        $optionalRuleMatched = $optionalRules->isEmpty();

        foreach ($rules as $rule) {
            $ruleMatches = $this->ruleMatches($rule, $context);

            if ($rule->is_required && ! $ruleMatches) {
                return false;
            }

            if (! $rule->is_required && $ruleMatches) {
                $optionalRuleMatched = true;
            }
        }

        return $optionalRuleMatched;
    }

    private function ruleMatches(ExperimentAudienceRule $rule, ExperimentContextData $context): bool
    {
        $actualValue = $this->resolveActualValue($rule, $context);

        return match ($rule->operator) {
            AudienceOperator::Equals => $actualValue === $rule->value,
            AudienceOperator::NotEquals => $actualValue !== $rule->value,
            AudienceOperator::Contains => $this->contains($actualValue, $rule->value),
            AudienceOperator::StartsWith => is_string($actualValue) && is_string($rule->value) && str_starts_with($actualValue, $rule->value),
            AudienceOperator::EndsWith => is_string($actualValue) && is_string($rule->value) && str_ends_with($actualValue, $rule->value),
            AudienceOperator::In => in_array($actualValue, $this->values($rule->value), true),
            AudienceOperator::NotIn => ! in_array($actualValue, $this->values($rule->value), true),
            AudienceOperator::Exists => $actualValue !== null,
            AudienceOperator::Missing => $actualValue === null,
        };
    }

    private function resolveActualValue(ExperimentAudienceRule $rule, ExperimentContextData $context): mixed
    {
        return match ($rule->type) {
            AudienceRuleType::Attribute => data_get($context->attributes, $rule->key),
            AudienceRuleType::Path => $context->path,
            AudienceRuleType::Query => data_get($context->query, $rule->key),
            AudienceRuleType::Referrer => $context->referrer,
            AudienceRuleType::Utm => data_get($context->utm, $rule->key),
            AudienceRuleType::Segment => in_array($rule->key, $context->segments, true) ? $rule->key : null,
        };
    }

    private function contains(mixed $actualValue, mixed $expectedValue): bool
    {
        if (is_array($actualValue)) {
            return in_array($expectedValue, $actualValue, true);
        }

        return is_string($actualValue)
            && is_string($expectedValue)
            && str_contains($actualValue, $expectedValue);
    }

    /**
     * @return list<mixed>
     */
    private function values(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        return [$value];
    }
}
