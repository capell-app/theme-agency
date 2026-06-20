<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionFilamentWidget;

final class PrivacyCenterOverviewFilamentWidgetContribution implements ExtensionContribution, RegistersExtensionFilamentWidget
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
