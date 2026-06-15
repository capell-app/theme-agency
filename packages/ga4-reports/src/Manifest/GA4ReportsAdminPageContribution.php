<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class GA4ReportsAdminPageContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
