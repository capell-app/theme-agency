<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class PublishingStudioHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
