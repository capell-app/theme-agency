<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class PasswordPolicyConsoleCommandsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
