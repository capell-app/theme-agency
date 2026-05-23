<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class DocumentLifecycleHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
