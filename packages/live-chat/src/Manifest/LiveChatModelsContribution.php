<?php

declare(strict_types=1);

namespace Capell\LiveChat\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class LiveChatModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
