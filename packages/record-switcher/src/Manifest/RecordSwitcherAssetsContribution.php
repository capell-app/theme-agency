<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionAsset;

final class RecordSwitcherAssetsContribution implements ExtensionContribution, RegistersExtensionAsset
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
