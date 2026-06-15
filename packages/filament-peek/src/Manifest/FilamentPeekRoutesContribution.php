<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;

final class FilamentPeekRoutesContribution implements ExtensionContribution, RegistersExtensionRoute
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
