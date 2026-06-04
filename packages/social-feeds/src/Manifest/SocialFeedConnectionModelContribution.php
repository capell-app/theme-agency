<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class SocialFeedConnectionModelContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
