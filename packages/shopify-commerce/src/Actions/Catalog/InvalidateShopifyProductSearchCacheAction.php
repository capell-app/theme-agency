<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsObject;

final class InvalidateShopifyProductSearchCacheAction
{
    use AsObject;

    public static function version(int $connectionId): int
    {
        return (int) Cache::get(self::versionKey($connectionId), 1);
    }

    public function handle(ShopifyConnection|int $connection): void
    {
        $connectionId = $connection instanceof ShopifyConnection ? (int) $connection->getKey() : $connection;
        $key = self::versionKey($connectionId);

        Cache::forever($key, ((int) Cache::get($key, 1)) + 1);
    }

    private static function versionKey(int $connectionId): string
    {
        return sprintf('capell-shopify-commerce.search.version.%d', $connectionId);
    }
}
