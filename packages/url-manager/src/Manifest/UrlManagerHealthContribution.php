<?php

declare(strict_types=1);

namespace Capell\UrlManager\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class UrlManagerHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
