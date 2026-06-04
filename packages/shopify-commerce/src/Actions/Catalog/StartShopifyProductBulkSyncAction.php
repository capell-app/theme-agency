<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Exceptions\ShopifyGraphqlException;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class StartShopifyProductBulkSyncAction
{
    use AsAction;

    public function handle(ShopifyConnection|int $connection): string
    {
        $connection = is_int($connection) ? ShopifyConnection::query()->findOrFail($connection) : $connection;

        return Cache::lock($this->lockKey((int) $connection->getKey()), 300)->block(10, function () use ($connection): string {
            $connection->refresh();

            if ($connection->status === ShopifyConnectionStatus::Revoked || ! $connection->isActive()) {
                return '';
            }

            try {
                $payload = ExecuteShopifyAdminGraphqlAction::run($connection, $this->mutation(), [
                    'query' => $this->bulkQuery(),
                ]);

                $bulkOperation = data_get($payload, 'data.bulkOperationRunQuery.bulkOperation');
                $userErrors = data_get($payload, 'data.bulkOperationRunQuery.userErrors', []);

                if (! is_array($bulkOperation) || ! is_string($bulkOperation['id'] ?? null) || (is_array($userErrors) && $userErrors !== [])) {
                    throw new ShopifyGraphqlException(is_array($userErrors) ? $userErrors : []);
                }

                $connection->forceFill([
                    'sync_status' => 'running',
                    'last_sync_started_at' => now(),
                    'bulk_operation_id' => $bulkOperation['id'],
                    'bulk_operation_url' => null,
                    'last_sync_error' => null,
                ])->save();

                return $bulkOperation['id'];
            } catch (Throwable $throwable) {
                $connection->forceFill([
                    'sync_status' => 'failed',
                    'status' => $throwable instanceof ShopifyGraphqlException ? ShopifyConnectionStatus::Error : $connection->status,
                    'last_sync_error' => SanitizeShopifySyncErrorAction::run($throwable, $connection),
                ])->save();

                throw $throwable;
            }
        });
    }

    private function lockKey(int $connectionId): string
    {
        return sprintf('capell-shopify-commerce.sync.%d', $connectionId);
    }

    private function mutation(): string
    {
        return <<<'GRAPHQL'
mutation ShopifyProductBulkSync($query: String!) {
  bulkOperationRunQuery(query: $query) {
    bulkOperation {
      id
      status
    }
    userErrors {
      field
      message
    }
  }
}
GRAPHQL;
    }

    private function bulkQuery(): string
    {
        return <<<'GRAPHQL'
{
  products {
    edges {
      node {
        id
        handle
        title
        status
        options {
          name
          values
        }
        featuredImage {
          url
          altText
        }
        variants {
          edges {
            node {
              id
              title
              price
              priceV2 {
                amount
                currencyCode
              }
              availableForSale
              selectedOptions {
                name
                value
              }
            }
          }
        }
      }
    }
  }
}
GRAPHQL;
    }
}
