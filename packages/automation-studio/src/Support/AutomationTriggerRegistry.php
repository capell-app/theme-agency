<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support;

use Capell\AutomationStudio\Data\AutomationTriggerDefinitionData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;

final class AutomationTriggerRegistry
{
    /** @var array<string, AutomationTriggerDefinitionData> */
    private array $definitions = [];

    public function register(AutomationTriggerDefinitionData $definition): void
    {
        $this->definitions[$definition->type->value] = $definition;
    }

    public function get(AutomationTriggerType $type): ?AutomationTriggerDefinitionData
    {
        return $this->definitions[$type->value] ?? null;
    }

    /**
     * @return array<string, AutomationTriggerDefinitionData>
     */
    public function all(): array
    {
        return $this->definitions;
    }
}
