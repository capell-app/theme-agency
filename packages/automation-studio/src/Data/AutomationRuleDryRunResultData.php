<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Data;

use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Spatie\LaravelData\Data;

final class AutomationRuleDryRunResultData extends Data
{
    /**
     * @param  list<string>  $actionKeys
     * @param  list<string>  $actionTypes
     */
    public function __construct(
        public readonly string $ruleKey,
        public readonly string $ruleName,
        public readonly AutomationTriggerType $triggerType,
        public readonly array $actionKeys,
        public readonly array $actionTypes,
    ) {}

    public function actionCount(): int
    {
        return count($this->actionKeys);
    }
}
