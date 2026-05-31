<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Data;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Spatie\LaravelData\Data;

final class AutomationActionDefinitionData extends Data
{
    /**
     * @param  class-string<AutomationActionHandler>|null  $handler
     */
    public function __construct(
        public readonly AutomationActionType $type,
        public readonly string $label,
        public readonly ?string $description = null,
        public readonly ?string $handler = null,
    ) {}
}
