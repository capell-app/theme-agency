<?php

declare(strict_types=1);

namespace Capell\Experiments\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ExperimentsHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
