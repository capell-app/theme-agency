<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class AgentDeliveryHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
