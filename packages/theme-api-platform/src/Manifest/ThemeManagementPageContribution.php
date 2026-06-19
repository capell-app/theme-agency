<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ApiPlatform\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class ThemeManagementPageContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
