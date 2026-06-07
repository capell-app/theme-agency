<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Enums\ShopifySyncStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

final class DisconnectShopifyStoreAction
{
    use AsAction;

    public function handle(ShopifyConnection $connection): void
    {
        Cache::lock(sprintf('capell-shopify-commerce.sync.%d', $connection->getKey()), 300)->block(10, function () use ($connection): void {
            $connection->forceFill([
                'status' => ShopifyConnectionStatus::Revoked,
                'access_token' => null,
                'sync_status' => ShopifySyncStatus::Revoked->value,
                'bulk_operation_id' => null,
                'bulk_operation_url' => null,
            ])->save();
        });
    }
}
