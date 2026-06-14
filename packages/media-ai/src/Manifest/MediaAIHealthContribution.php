<?php

declare(strict_types=1);

namespace Capell\MediaAI\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class MediaAIHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
