<?php

declare(strict_types=1);

namespace Capell\Events\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionRenderHook;

final class EventsRenderHookContribution implements ExtensionContribution, RegistersExtensionRenderHook
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
