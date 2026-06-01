<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class SystemHealthPageContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
