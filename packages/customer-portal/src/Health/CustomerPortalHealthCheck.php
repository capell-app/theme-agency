<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class CustomerPortalHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
