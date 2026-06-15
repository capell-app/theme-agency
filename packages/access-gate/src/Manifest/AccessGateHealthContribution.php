<?php

declare(strict_types=1);

namespace Capell\AccessGate\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class AccessGateHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
