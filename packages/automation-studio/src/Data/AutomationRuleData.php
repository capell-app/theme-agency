<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Data;

use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Spatie\LaravelData\Data;

final class AutomationRuleData extends Data
{
    /**
     * @param  list<AutomationRuleActionData>  $actions
     * @param  array<string, mixed>  $conditions
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly AutomationTriggerType $triggerType,
        public readonly array $actions,
        public readonly AutomationRuleStatus $status = AutomationRuleStatus::Active,
        public readonly array $conditions = [],
    ) {}
}
