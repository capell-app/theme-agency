<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ShopifyCommerceHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
