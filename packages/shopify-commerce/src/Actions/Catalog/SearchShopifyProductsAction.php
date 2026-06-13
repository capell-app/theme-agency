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
use RuntimeException;

/**
 * @method static EloquentCollection<int, ShopifyProduct> run(string $term, int $limit, ShopifyConnection $connection)
 */
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

        $cachedProductIds = Cache::get($cacheKey);
        $productIds = $this->integerList($cachedProductIds);

        if (is_array($productIds)) {
            return $this->productsByIds($productIds);
        }

        if ($cachedProductIds !== null) {
            Cache::forget($cacheKey);
        }

        $productIds = $this->productIds($connection, $normalisedTerm, $limit);

        Cache::put($cacheKey, $productIds, now()->addMinutes($this->cacheTtlMinutes()));

        return $this->productsByIds($productIds);
    }

    /**
     * @return list<int>
     */
    private function productIds(ShopifyConnection $connection, string $normalisedTerm, int $limit): array
    {
        $localProducts = $this->localResults($connection, $normalisedTerm, $limit);

        if ($localProducts->isNotEmpty() || $normalisedTerm === '') {
            return $this->productKeys($localProducts);
        }

        $this->fetchAndPersistLiveResults($connection, $normalisedTerm, $limit);

        return $this->productKeys($this->localResults($connection, $normalisedTerm, $limit));
    }

    /**
     * @param  list<int>  $productIds
     * @return EloquentCollection<int, ShopifyProduct>
     */
    private function productsByIds(array $productIds): EloquentCollection
    {
        if ($productIds === []) {
            return new EloquentCollection;
        }

        $products = ShopifyProduct::query()
            ->withCount('variants')
            ->whereKey($productIds)
            ->get()
            ->keyBy(fn (ShopifyProduct $product): int => $this->productKey($product));

        return new EloquentCollection(
            collect($productIds)
                ->map(fn (int $productId): ?ShopifyProduct => $products->get($productId))
                ->filter(fn (?ShopifyProduct $product): bool => $product instanceof ShopifyProduct)
                ->values()
                ->all(),
        );
    }

    /**
     * @return list<int>|null
     */
    private function integerList(mixed $value): ?array
    {
        if (! is_array($value) || ! array_is_list($value)) {
            return null;
        }

        $productIds = [];

        foreach ($value as $productId) {
            if (! is_int($productId)) {
                return null;
            }

            $productIds[] = $productId;
        }

        return $productIds;
    }

    /**
     * @param  EloquentCollection<int, ShopifyProduct>  $products
     * @return list<int>
     */
    private function productKeys(EloquentCollection $products): array
    {
        $keys = [];

        foreach ($products as $product) {
            $keys[] = $this->productKey($product);
        }

        return $keys;
    }

    private function productKey(ShopifyProduct $product): int
    {
        $key = $product->getKey();

        if (is_int($key)) {
            return $key;
        }

        if (is_string($key) && ctype_digit($key)) {
            return (int) $key;
        }

        throw new RuntimeException('Shopify product key must be an integer.');
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

                PersistShopifyProductAction::run($connection, $this->mapProductNode($this->stringKeyedArray($node)), $syncedAt);

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
        $variantNodes = data_get($node, 'variants.nodes');
        $variantNodes = is_array($variantNodes) ? $variantNodes : [];

        $options = array_values(collect($optionNodes)
            ->filter(static fn (mixed $option): bool => is_array($option))
            ->map(fn (array $option): ShopifyProductOptionData => new ShopifyProductOptionData(
                name: $this->stringValue($option['name'] ?? ''),
                values: array_values(array_filter(
                    is_array($option['values'] ?? null) ? $option['values'] : [],
                    static fn (mixed $value): bool => is_string($value) && $value !== '',
                )),
            ))
            ->all());

        $variants = array_values(collect($variantNodes)
            ->filter(static fn (mixed $variant): bool => is_array($variant) && is_string($variant['id'] ?? null))
            ->map(fn (array $variant): ShopifyProductVariantData => $this->mapVariantNode($this->stringKeyedArray($variant)))
            ->all());

        return new ShopifyProductData(
            shopifyGid: $this->stringValue($node['id']),
            handle: $this->stringValue($node['handle'] ?? ''),
            title: $this->stringValue($node['title'] ?? ''),
            status: mb_strtolower($this->stringValue($node['status'] ?? 'unknown', 'unknown')),
            options: $options,
            featuredImage: is_array($node['featuredImage'] ?? null) ? $this->stringKeyedArray($node['featuredImage']) : null,
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

        $selectedOptions = array_values(collect($selectedOptionNodes)
            ->filter(static fn (mixed $option): bool => is_array($option))
            ->map(fn (array $option): ShopifyProductOptionData => new ShopifyProductOptionData(
                name: $this->stringValue($option['name'] ?? ''),
                value: is_string($option['value'] ?? null) ? $option['value'] : null,
            ))
            ->all());

        return new ShopifyProductVariantData(
            shopifyGid: $this->stringValue($variant['id']),
            title: $this->stringValue($variant['title'] ?? ''),
            priceAmount: $this->stringValue($priceV2['amount'] ?? $variant['price'] ?? '0', '0'),
            priceCurrency: $this->stringValue($priceV2['currencyCode'] ?? config('capell-shopify-commerce.default_currency', 'USD'), 'USD'),
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

    private function stringValue(mixed $value, string $fallback = ''): string
    {
        return is_scalar($value) ? (string) $value : $fallback;
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<string, mixed>
     */
    private function stringKeyedArray(array $values): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
