<?php

declare(strict_types=1);

namespace Capell\Newsletter\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class NewsletterHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
