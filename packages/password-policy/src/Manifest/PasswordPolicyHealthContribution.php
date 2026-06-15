<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class PasswordPolicyHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
