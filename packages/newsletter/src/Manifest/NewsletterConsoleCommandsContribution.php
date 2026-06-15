<?php

declare(strict_types=1);

namespace Capell\Newsletter\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class NewsletterConsoleCommandsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
