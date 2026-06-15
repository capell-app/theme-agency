<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class PrivacyCenterHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
