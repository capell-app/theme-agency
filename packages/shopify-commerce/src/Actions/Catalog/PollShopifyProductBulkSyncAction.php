<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

final class PollShopifyProductBulkSyncAction
{
    use AsAction;

    public function handle(ShopifyConnection|int $connection): string
    {
        $connection = is_int($connection) ? ShopifyConnection::query()->findOrFail($connection) : $connection;

        return Cache::lock(sprintf('capell-shopify-commerce.sync.%d', $connection->getKey()), 300)->block(10, function () use ($connection): string {
            $connection->refresh();

            if ($connection->status === ShopifyConnectionStatus::Revoked) {
                return 'REVOKED';
            }

            $payload = ExecuteShopifyAdminGraphqlAction::run($connection, $this->query());
            $operation = data_get($payload, 'data.currentBulkOperation');

            if (! is_array($operation)) {
                return 'NONE';
            }

            $status = is_string($operation['status'] ?? null) ? $operation['status'] : 'UNKNOWN';

            if ($status === 'COMPLETED' && is_string($operation['url'] ?? null)) {
                $connection->forceFill([
                    'sync_status' => 'completed',
                    'bulk_operation_id' => is_string($operation['id'] ?? null) ? $operation['id'] : $connection->bulk_operation_id,
                    'bulk_operation_url' => $operation['url'],
                    'last_sync_error' => null,
                ])->save();

                return $status;
            }

            if (in_array($status, ['FAILED', 'CANCELED'], true)) {
                $connection->forceFill([
                    'sync_status' => mb_strtolower($status),
                    'status' => ShopifyConnectionStatus::Error,
                    'last_sync_error' => is_string($operation['errorCode'] ?? null) ? $operation['errorCode'] : $status,
                ])->save();

                return $status;
            }

            $connection->forceFill([
                'sync_status' => 'running',
                'bulk_operation_id' => is_string($operation['id'] ?? null) ? $operation['id'] : $connection->bulk_operation_id,
            ])->save();

            return $status;
        });
    }

    private function query(): string
    {
        return <<<'GRAPHQL'
query ShopifyCurrentBulkOperation {
  currentBulkOperation {
    id
    status
    errorCode
    url
  }
}
GRAPHQL;
    }
}
