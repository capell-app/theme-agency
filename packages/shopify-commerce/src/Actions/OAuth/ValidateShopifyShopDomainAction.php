<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Lorisleiva\Actions\Concerns\AsAction;

final class ValidateShopifyShopDomainAction
{
    use AsAction;

    public function handle(mixed $shop): bool
    {
        if (! is_string($shop)) {
            return false;
        }

        return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $shop) === 1;
    }
}
