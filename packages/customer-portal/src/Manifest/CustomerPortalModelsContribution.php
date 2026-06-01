<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class CustomerPortalModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
