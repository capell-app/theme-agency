<?php

declare(strict_types=1);

namespace Capell\PublicActions\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class PublicActionsConsoleCommandsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
