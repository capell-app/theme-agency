<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;

final class GA4ReportsSettingsContribution implements ExtensionContribution, RegistersExtensionSetting
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
