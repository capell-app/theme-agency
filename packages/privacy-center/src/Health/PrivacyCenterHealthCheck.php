<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class PrivacyCenterHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
