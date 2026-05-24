<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Knowledge\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ThemeKnowledgeHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
