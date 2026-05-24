<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LocalServices\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ThemeLocalServicesHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
