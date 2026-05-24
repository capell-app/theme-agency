<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Healthcare\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ThemeHealthcareHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
