<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Portfolio\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ThemePortfolioHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
