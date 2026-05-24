<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncShopifyProductsAction
{
    use AsAction;

    public function handle(ShopifyConnection|int $connection): ?string
    {
        $connection = is_int($connection) ? ShopifyConnection::query()->findOrFail($connection) : $connection;

        $connection->refresh();

        if ($connection->status === ShopifyConnectionStatus::Revoked || ! $connection->isActive()) {
            return null;
        }

        if (in_array($connection->sync_status, ['running', 'importing'], true)) {
            return is_string($connection->bulk_operation_id) ? $connection->bulk_operation_id : null;
        }

        $connection->forceFill([
            'sync_status' => 'queued',
            'last_sync_queued_at' => now(),
            'last_sync_error' => null,
        ])->save();

        return StartShopifyProductBulkSyncAction::run($connection);
    }

    /**
     * @return array<int, WithoutOverlapping>
     */
    public function getJobMiddleware(ShopifyConnection|int $connection): array
    {
        $connectionId = $connection instanceof ShopifyConnection ? (int) $connection->getKey() : $connection;

        return [
            (new WithoutOverlapping($this->lockKey($connectionId)))->expireAfter(21_600),
        ];
    }

    private function lockKey(int $connectionId): string
    {
        return sprintf('capell-shopify-commerce.sync.%d', $connectionId);
    }
}
