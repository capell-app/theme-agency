<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Data;

use Capell\AutomationStudio\Enums\AutomationActionType;
use Spatie\LaravelData\Data;

final class AutomationRuleActionData extends Data
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function __construct(
        public readonly string $key,
        public readonly AutomationActionType $type,
        public readonly array $settings = [],
    ) {}
}
