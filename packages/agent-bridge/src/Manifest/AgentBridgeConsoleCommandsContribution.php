<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class AgentBridgeConsoleCommandsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
