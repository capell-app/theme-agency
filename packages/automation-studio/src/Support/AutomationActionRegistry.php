<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionDefinitionData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use InvalidArgumentException;

final class AutomationActionRegistry
{
    /** @var array<string, AutomationActionDefinitionData> */
    private array $definitions = [];

    /** @var array<string, AutomationActionHandler|class-string<AutomationActionHandler>> */
    private array $handlers = [];

    public function registerDefinition(AutomationActionDefinitionData $definition): void
    {
        $this->definitions[$definition->type->value] = $definition;

        if ($definition->handler !== null) {
            $this->registerHandler($definition->type, $definition->handler);
        }
    }

    /**
     * @param  AutomationActionHandler|class-string<AutomationActionHandler>  $handler
     */
    public function registerHandler(AutomationActionType $type, AutomationActionHandler|string $handler): void
    {
        throw_unless(
            $handler instanceof AutomationActionHandler || is_a($handler, AutomationActionHandler::class, true),
            InvalidArgumentException::class,
            'Automation action handlers must implement AutomationActionHandler.',
        );

        $this->handlers[$type->value] = $handler;
    }

    public function definition(AutomationActionType $type): ?AutomationActionDefinitionData
    {
        return $this->definitions[$type->value] ?? null;
    }

    public function handler(AutomationActionType $type): ?AutomationActionHandler
    {
        $handler = $this->handlers[$type->value] ?? null;

        if ($handler === null) {
            return null;
        }

        return is_string($handler) ? resolve($handler) : $handler;
    }

    /**
     * @return array<string, AutomationActionDefinitionData>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }
}
