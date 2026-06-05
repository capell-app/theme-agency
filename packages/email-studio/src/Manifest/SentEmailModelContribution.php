<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class SentEmailModelContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
