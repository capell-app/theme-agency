<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class AiDiscoveryPageContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
