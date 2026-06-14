<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;

final class LoginAuditSettingsContribution implements ExtensionContribution, RegistersExtensionSetting
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
