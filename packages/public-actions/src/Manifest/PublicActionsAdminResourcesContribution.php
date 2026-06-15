<?php

declare(strict_types=1);

namespace Capell\PublicActions\Manifest;

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;

final class PublicActionsAdminResourcesContribution implements RegistersExtensionAdminResource
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
