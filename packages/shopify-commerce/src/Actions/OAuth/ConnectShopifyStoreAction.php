<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Capell\ShopifyCommerce\Actions\Catalog\SyncShopifyProductsAction;
use Capell\ShopifyCommerce\Data\ShopifyTokenExchangeResponseData;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Enums\ShopifySyncStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Contracts\Auth\Authenticatable;
use Lorisleiva\Actions\Concerns\AsAction;

final class ConnectShopifyStoreAction
{
    use AsAction;

    public function handle(string $shopDomain, ShopifyTokenExchangeResponseData $tokenData, Authenticatable $user, ?int $siteId = null): ShopifyConnection
    {
        /** @var ShopifyConnection $connection */
        $connection = ShopifyConnection::query()->updateOrCreate(
            [
                'site_id' => $siteId,
                'shop_domain' => $shopDomain,
            ],
            [
                'status' => ShopifyConnectionStatus::Active,
                'access_token' => $tokenData->accessToken,
                'scopes' => $tokenData->scopes,
                'connected_by_user_id' => $user->getAuthIdentifier(),
                'sync_status' => ShopifySyncStatus::Queued->value,
                'last_sync_queued_at' => now(),
                'last_sync_error' => null,
            ],
        );

        SyncShopifyProductsAction::dispatch((int) $connection->getKey());

        return $connection;
    }
}
