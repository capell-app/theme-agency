<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RunsExtensionMigration;

final class AgentBridgeMigrationsContribution implements ExtensionContribution, RunsExtensionMigration
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
