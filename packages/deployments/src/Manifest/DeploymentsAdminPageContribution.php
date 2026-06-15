<?php

declare(strict_types=1);

namespace Capell\Deployments\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class DeploymentsAdminPageContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
