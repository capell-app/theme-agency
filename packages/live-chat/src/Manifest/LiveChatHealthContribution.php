<?php

declare(strict_types=1);

namespace Capell\LiveChat\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class LiveChatHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
