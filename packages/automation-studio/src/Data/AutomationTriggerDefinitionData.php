<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Data;

use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Spatie\LaravelData\Data;

final class AutomationTriggerDefinitionData extends Data
{
    public function __construct(
        public readonly AutomationTriggerType $type,
        public readonly string $label,
        public readonly ?string $description = null,
        public readonly ?string $eventClass = null,
    ) {}
}
