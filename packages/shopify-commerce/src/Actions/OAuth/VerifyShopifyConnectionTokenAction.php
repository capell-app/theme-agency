<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static bool run(ShopifyConnection $connection)
 */
final class VerifyShopifyConnectionTokenAction
{
    use AsAction;

    private const string TOKEN_PROBE_QUERY = <<<'GRAPHQL'
query capellShopifyCommerceHealthProbe {
  shop {
    name
  }
}
GRAPHQL;

    public function handle(ShopifyConnection $connection): bool
    {
        try {
            $payload = ExecuteShopifyAdminGraphqlAction::run($connection, self::TOKEN_PROBE_QUERY);
        } catch (Throwable) {
            return false;
        }

        return is_string(data_get($payload, 'data.shop.name'));
    }
}
