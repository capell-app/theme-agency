<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class KnowledgeBaseHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
