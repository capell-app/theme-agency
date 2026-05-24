<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Education\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ThemeEducationHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
