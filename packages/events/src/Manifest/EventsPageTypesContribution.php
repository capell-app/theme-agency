<?php

declare(strict_types=1);

namespace Capell\Events\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionPageType;

final class EventsPageTypesContribution implements ExtensionContribution, RegistersExtensionPageType
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
