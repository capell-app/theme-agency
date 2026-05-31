<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class AutomationStudioHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
