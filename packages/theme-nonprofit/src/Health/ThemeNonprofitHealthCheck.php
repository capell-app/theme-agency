<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Nonprofit\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ThemeNonprofitHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
