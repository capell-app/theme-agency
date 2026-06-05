<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Customers;

use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(ShopifyConnection|int $connection)
 */
final class SyncShopifyCustomersAction
{
    use AsAction;

    public function handle(ShopifyConnection|int $connection): int
    {
        $connection = is_int($connection) ? ShopifyConnection::query()->findOrFail($connection) : $connection;

        $syncedCount = Cache::lock($this->lockKey($this->intValue($connection->getKey())), 300)->block(10, function () use ($connection): int {
            $connection->refresh();

            if ($connection->status === ShopifyConnectionStatus::Revoked || ! $connection->isActive()) {
                return 0;
            }

            $syncedCount = 0;
            $afterCursor = null;

            for ($page = 1; $page <= $this->maxPages(); $page++) {
                $payload = ExecuteShopifyAdminGraphqlAction::run($connection, $this->query(), [
                    'first' => $this->pageSize(),
                    'after' => $afterCursor,
                ]);

                $customers = data_get($payload, 'data.customers.edges', []);
                if (! is_array($customers)) {
                    break;
                }

                foreach ($customers as $edge) {
                    $customer = data_get($edge, 'node');

                    if (! is_array($customer)) {
                        continue;
                    }

                    UpsertShopifyCustomerAction::run($connection, [
                        ...$customer,
                        'synced_at' => now(),
                    ]);

                    $syncedCount++;
                }

                $pageInfo = data_get($payload, 'data.customers.pageInfo', []);
                $hasNextPage = is_array($pageInfo) && ($pageInfo['hasNextPage'] ?? false) === true;
                $nextCursor = is_array($pageInfo) && is_string($pageInfo['endCursor'] ?? null)
                    ? $pageInfo['endCursor']
                    : null;

                if (! $hasNextPage || $nextCursor === null || $nextCursor === $afterCursor) {
                    break;
                }

                $afterCursor = $nextCursor;
            }

            return $syncedCount;
        });

        return is_int($syncedCount) ? $syncedCount : 0;
    }

    private function pageSize(): int
    {
        return min(250, max(1, $this->intValue(config('capell-shopify-commerce.customer_sync_page_size', 100), 100)));
    }

    private function maxPages(): int
    {
        return max(1, $this->intValue(config('capell-shopify-commerce.customer_sync_max_pages', 100), 100));
    }

    private function lockKey(int $connectionId): string
    {
        return sprintf('capell-shopify-commerce.customers.sync.%d', $connectionId);
    }

    private function intValue(mixed $value, int $default = 0): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    private function query(): string
    {
        return <<<'GRAPHQL'
query ShopifyCustomerSync($first: Int!, $after: String) {
  customers(first: $first, after: $after) {
    edges {
      cursor
      node {
        id
        email
        firstName
        lastName
        phone
        acceptsMarketing
        emailMarketingConsent {
          marketingState
        }
        numberOfOrders
        amountSpent {
          amount
          currencyCode
        }
        updatedAt
      }
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}
GRAPHQL;
    }
}
