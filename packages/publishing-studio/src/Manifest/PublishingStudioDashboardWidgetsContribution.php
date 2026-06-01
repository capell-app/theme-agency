<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;

final class PublishingStudioDashboardWidgetsContribution implements ExtensionContribution, RegistersExtensionWidget
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
