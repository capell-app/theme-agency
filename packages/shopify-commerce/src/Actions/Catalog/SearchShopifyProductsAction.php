<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

final class SearchShopifyProductsAction
{
    use AsAction;

    /**
     * @return EloquentCollection<int, ShopifyProduct>
     */
    public function handle(string $term, int $limit, ShopifyConnection $connection): EloquentCollection
    {
        if (! $connection->isActive()) {
            return new EloquentCollection;
        }

        $normalisedTerm = trim($term);
        $limit = max(1, min(100, $limit));
        $connectionId = (int) $connection->getKey();
        $cacheVersion = InvalidateShopifyProductSearchCacheAction::version($connectionId);
        $cacheKey = sprintf('capell-shopify-commerce.search.%d.%d.%s.%d', $connectionId, $cacheVersion, hash('sha256', mb_strtolower($normalisedTerm)), $limit);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheTtlMinutes()), function () use ($connection, $normalisedTerm, $limit): EloquentCollection {
            $localProducts = $this->localResults($connection, $normalisedTerm, $limit);

            if ($localProducts->isNotEmpty() || $normalisedTerm === '') {
                return $localProducts;
            }

            $this->fetchAndPersistLiveResults($connection, $normalisedTerm);

            return $this->localResults($connection, $normalisedTerm, $limit);
        });
    }

    /**
     * @return EloquentCollection<int, ShopifyProduct>
     */
    private function localResults(ShopifyConnection $connection, string $term, int $limit): EloquentCollection
    {
        return ShopifyProduct::query()
            ->withCount('variants')
            ->where('connection_id', $connection->getKey())
            ->when($term !== '', function (Builder $query) use ($term): void {
                $query->where(function (Builder $nestedQuery) use ($term): void {
                    $prefix = mb_strtolower($term) . '%';

                    $nestedQuery
                        ->where('search_text', 'like', $prefix)
                        ->orWhere('title', 'like', $term . '%')
                        ->orWhere('handle', 'like', $term . '%');
                });
            })
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    private function fetchAndPersistLiveResults(ShopifyConnection $connection, string $term): void
    {
        $payload = ExecuteShopifyAdminGraphqlAction::run($connection, $this->query(), [
            'query' => sprintf('title:*%s* OR handle:*%s*', addcslashes($term, '"\\'), addcslashes($term, '"\\')),
        ]);

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $products = is_array($data['products'] ?? null) ? $data['products'] : [];
        $nodes = is_array($products['nodes'] ?? null) ? $products['nodes'] : [];

        $changed = DB::transaction(function () use ($connection, $nodes): bool {
            $changed = false;

            foreach ($nodes as $node) {
                if (! is_array($node)) {
                    continue;
                }

                if (! is_string($node['id'] ?? null)) {
                    continue;
                }

                $title = (string) ($node['title'] ?? '');
                $handle = (string) ($node['handle'] ?? '');

                ShopifyProduct::query()->updateOrCreate(
                    [
                        'connection_id' => $connection->getKey(),
                        'shopify_gid' => $node['id'],
                    ],
                    [
                        'handle' => $handle,
                        'title' => $title,
                        'search_text' => ShopifyProduct::searchableText($title, $handle),
                        'status' => mb_strtolower((string) ($node['status'] ?? 'unknown')),
                        'featured_image' => is_array($node['featuredImage'] ?? null) ? $node['featuredImage'] : null,
                        'synced_at' => now(),
                    ],
                );

                $changed = true;
            }

            return $changed;
        });

        if ($changed) {
            InvalidateShopifyProductSearchCacheAction::run($connection);
        }
    }

    private function query(): string
    {
        return <<<'GRAPHQL'
query ShopifyProductSearch($query: String!) {
  products(first: 20, query: $query) {
    nodes {
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
}
GRAPHQL;
    }

    private function cacheTtlMinutes(): int
    {
        if (app()->bound(ShopifyCommerceSettings::class)) {
            $settings = resolve(ShopifyCommerceSettings::class);

            return max(1, $settings->search_cache_ttl_minutes);
        }

        return 5;
    }
}
