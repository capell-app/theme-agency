<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;

final class CampaignBlockComponentsContribution implements ExtensionContribution, RegistersExtensionFrontendComponent
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
