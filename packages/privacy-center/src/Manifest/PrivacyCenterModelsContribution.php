<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class PrivacyCenterModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
