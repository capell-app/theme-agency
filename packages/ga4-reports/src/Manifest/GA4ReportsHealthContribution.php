<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class GA4ReportsHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
