<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class AgentDeliveryContractsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
