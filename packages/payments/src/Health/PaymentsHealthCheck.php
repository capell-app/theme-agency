<?php

declare(strict_types=1);

namespace Capell\Payments\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class PaymentsHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
