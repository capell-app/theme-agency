<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;

final class SocialFeedWidgetContribution implements ExtensionContribution, RegistersExtensionFrontendComponent
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
