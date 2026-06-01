<?php

declare(strict_types=1);

namespace Capell\UrlManager\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class NotFoundOpportunitiesPageContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
