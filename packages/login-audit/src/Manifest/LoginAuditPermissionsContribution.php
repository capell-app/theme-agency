<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionPermission;

final class LoginAuditPermissionsContribution implements ExtensionContribution, RegistersExtensionPermission
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
