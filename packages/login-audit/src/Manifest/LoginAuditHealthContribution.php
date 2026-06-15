<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class LoginAuditHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
