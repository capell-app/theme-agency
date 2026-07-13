<?php

declare(strict_types=1);

namespace Capell\ThemeAgency\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class ThemeManagementPageContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^1.0';
    }
}
