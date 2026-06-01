<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class CampaignBlockConfiguratorsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
