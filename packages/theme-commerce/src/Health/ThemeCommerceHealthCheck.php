<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Commerce\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ThemeCommerceHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
