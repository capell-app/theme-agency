<?php

declare(strict_types=1);

namespace Capell\Deployments\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;

final class DeploymentsDashboardWidgetContribution implements ExtensionContribution, RegistersExtensionWidget
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
