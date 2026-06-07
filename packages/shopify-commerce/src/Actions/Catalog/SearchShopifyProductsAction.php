<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Data\ShopifyProductData;
use Capell\ShopifyCommerce\Data\ShopifyProductOptionData;
use Capell\ShopifyCommerce\Data\ShopifyProductVariantData;
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

            $this->fetchAndPersistLiveResults($connection, $normalisedTerm, $limit);

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

    private function fetchAndPersistLiveResults(ShopifyConnection $connection, string $term, int $limit): void
    {
        $payload = ExecuteShopifyAdminGraphqlAction::run($connection, $this->query(), [
            'first' => $limit,
            'query' => sprintf('title:*%s* OR handle:*%s*', addcslashes($term, '"\\'), addcslashes($term, '"\\')),
        ]);

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $products = is_array($data['products'] ?? null) ? $data['products'] : [];
        $nodes = is_array($products['nodes'] ?? null) ? $products['nodes'] : [];

        $changed = DB::transaction(function () use ($connection, $nodes): bool {
            $changed = false;
            $syncedAt = now();

            foreach ($nodes as $node) {
                if (! is_array($node)) {
                    continue;
                }

                if (! is_string($node['id'] ?? null)) {
                    continue;
                }

                PersistShopifyProductAction::run($connection, $this->mapProductNode($node), $syncedAt);

                $changed = true;
            }

            return $changed;
        });

        if ($changed) {
            InvalidateShopifyProductSearchCacheAction::run($connection);
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function mapProductNode(array $node): ShopifyProductData
    {
        $optionNodes = is_array($node['options'] ?? null) ? $node['options'] : [];
        $variantNodes = is_array(data_get($node, 'variants.nodes')) ? data_get($node, 'variants.nodes') : [];

        $options = collect($optionNodes)
            ->filter(static fn (mixed $option): bool => is_array($option))
            ->map(static fn (array $option): ShopifyProductOptionData => new ShopifyProductOptionData(
                name: (string) ($option['name'] ?? ''),
                values: array_values(array_filter(
                    is_array($option['values'] ?? null) ? $option['values'] : [],
                    static fn (mixed $value): bool => is_string($value) && $value !== '',
                )),
            ))
            ->values()
            ->all();

        $variants = collect($variantNodes)
            ->filter(static fn (mixed $variant): bool => is_array($variant) && is_string($variant['id'] ?? null))
            ->map(fn (array $variant): ShopifyProductVariantData => $this->mapVariantNode($variant))
            ->values()
            ->all();

        return new ShopifyProductData(
            shopifyGid: (string) $node['id'],
            handle: (string) ($node['handle'] ?? ''),
            title: (string) ($node['title'] ?? ''),
            status: mb_strtolower((string) ($node['status'] ?? 'unknown')),
            options: $options,
            featuredImage: is_array($node['featuredImage'] ?? null) ? $node['featuredImage'] : null,
            variants: $variants,
            rawSnapshot: $node,
        );
    }

    /**
     * @param  array<string, mixed>  $variant
     */
    private function mapVariantNode(array $variant): ShopifyProductVariantData
    {
        $priceV2 = is_array($variant['priceV2'] ?? null) ? $variant['priceV2'] : [];
        $selectedOptionNodes = is_array($variant['selectedOptions'] ?? null) ? $variant['selectedOptions'] : [];

        $selectedOptions = collect($selectedOptionNodes)
            ->filter(static fn (mixed $option): bool => is_array($option))
            ->map(static fn (array $option): ShopifyProductOptionData => new ShopifyProductOptionData(
                name: (string) ($option['name'] ?? ''),
                value: is_string($option['value'] ?? null) ? $option['value'] : null,
            ))
            ->values()
            ->all();

        return new ShopifyProductVariantData(
            shopifyGid: (string) $variant['id'],
            title: (string) ($variant['title'] ?? ''),
            priceAmount: (string) ($priceV2['amount'] ?? $variant['price'] ?? '0'),
            priceCurrency: (string) ($priceV2['currencyCode'] ?? config('capell-shopify-commerce.default_currency', 'USD')),
            availableForSale: ($variant['availableForSale'] ?? false) === true,
            selectedOptions: $selectedOptions,
        );
    }

    private function query(): string
    {
        return <<<'GRAPHQL'
query ShopifyProductSearch($query: String!, $first: Int!) {
  products(first: $first, query: $query) {
    nodes {
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
      variants(first: 100) {
        nodes {
          id
          title
          availableForSale
          priceV2 {
            amount
            currencyCode
          }
          selectedOptions {
            name
            value
          }
        }
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
