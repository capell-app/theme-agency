<?php

declare(strict_types=1);

namespace Capell\Experiments\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class ExperimentsModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
