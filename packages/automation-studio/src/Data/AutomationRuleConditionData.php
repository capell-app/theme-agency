<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Data;

use Capell\AutomationStudio\Enums\AutomationRuleConditionOperator;
use Spatie\LaravelData\Data;

final class AutomationRuleConditionData extends Data
{
    public function __construct(
        public readonly string $field,
        public readonly AutomationRuleConditionOperator $operator = AutomationRuleConditionOperator::Equals,
        public readonly mixed $value = null,
    ) {}

    /**
     * @param  array<string, mixed>  $condition
     */
    public static function fromArray(array $condition): ?self
    {
        $field = $condition['field'] ?? null;

        if (! is_string($field) || trim($field) === '') {
            return null;
        }

        $operator = $condition['operator'] ?? AutomationRuleConditionOperator::Equals->value;

        return new self(
            field: trim($field),
            operator: $operator instanceof AutomationRuleConditionOperator
                ? $operator
                : AutomationRuleConditionOperator::tryFrom((string) $operator) ?? AutomationRuleConditionOperator::Equals,
            value: $condition['value'] ?? null,
        );
    }
}
