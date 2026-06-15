<?php

declare(strict_types=1);

namespace Capell\PublicActions\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class PublicActionsHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
