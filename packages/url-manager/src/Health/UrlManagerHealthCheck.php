<?php

declare(strict_types=1);

namespace Capell\UrlManager\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class UrlManagerHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
