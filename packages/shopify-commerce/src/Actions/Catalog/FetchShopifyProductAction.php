<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

final class FetchShopifyProductAction
{
    use AsAction;

    public function handle(string $shopifyGid, ShopifyConnection $connection): ?ShopifyProduct
    {
        if (! $connection->isActive()) {
            return null;
        }

        $payload = ExecuteShopifyAdminGraphqlAction::run($connection, $this->query(), [
            'id' => $shopifyGid,
        ]);

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $product = is_array($data['product'] ?? null) ? $data['product'] : null;

        if ($product === null || ! is_string($product['id'] ?? null)) {
            return null;
        }

        $model = DB::transaction(function () use ($connection, $product): ShopifyProduct {
            $title = (string) ($product['title'] ?? '');
            $handle = (string) ($product['handle'] ?? '');

            /** @var ShopifyProduct $model */
            $model = ShopifyProduct::query()->updateOrCreate(
                [
                    'connection_id' => $connection->getKey(),
                    'shopify_gid' => $product['id'],
                ],
                [
                    'handle' => $handle,
                    'title' => $title,
                    'search_text' => ShopifyProduct::searchableText($title, $handle),
                    'status' => mb_strtolower((string) ($product['status'] ?? 'unknown')),
                    'featured_image' => is_array($product['featuredImage'] ?? null) ? $product['featuredImage'] : null,
                    'synced_at' => now(),
                ],
            );

            return $model->refresh();
        });

        InvalidateShopifyProductSearchCacheAction::run($connection);

        return $model;
    }

    private function query(): string
    {
        return <<<'GRAPHQL'
query ShopifyProduct($id: ID!) {
  product(id: $id) {
    id
    handle
    title
    status
    featuredImage {
      url
      altText
    }
  }
}
GRAPHQL;
    }
}
