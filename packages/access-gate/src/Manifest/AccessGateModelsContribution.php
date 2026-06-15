<?php

declare(strict_types=1);

namespace Capell\AccessGate\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class AccessGateModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
