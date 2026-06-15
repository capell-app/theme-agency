<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ShopifyCommerceHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
