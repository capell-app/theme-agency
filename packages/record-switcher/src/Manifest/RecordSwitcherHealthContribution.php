<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class RecordSwitcherHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
