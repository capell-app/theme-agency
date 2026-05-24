<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Data\ShopifyProductData;
use Capell\ShopifyCommerce\Data\ShopifyProductOptionData;
use Capell\ShopifyCommerce\Data\ShopifyProductVariantData;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Throwable;

final class ImportShopifyProductBulkSyncAction
{
    use AsAction;

    public function handle(ShopifyConnection|int $connection): int
    {
        $connection = is_int($connection) ? ShopifyConnection::query()->findOrFail($connection) : $connection;

        return Cache::lock(sprintf('capell-shopify-commerce.sync.%d', $connection->getKey()), 300)->block(10, function () use ($connection): int {
            $connection->refresh();

            if ($connection->status === ShopifyConnectionStatus::Revoked) {
                return 0;
            }

            throw_if(! is_string($connection->bulk_operation_url) || $connection->bulk_operation_url === '', RuntimeException::class, 'Shopify bulk operation URL is missing.');

            try {
                $connection->forceFill(['sync_status' => 'importing'])->save();

                $products = $this->downloadProducts($connection->bulk_operation_url);
                $seenProductGids = [];

                DB::transaction(function () use ($connection, $products, &$seenProductGids): void {
                    foreach ($products as $product) {
                        $seenProductGids[] = $product->shopifyGid;
                        $this->persistProduct($connection, $product);
                    }

                    ShopifyProduct::query()
                        ->where('connection_id', $connection->getKey())
                        ->whereNotIn('shopify_gid', $seenProductGids)
                        ->delete();
                });

                $connection->refresh();

                if ($connection->status !== ShopifyConnectionStatus::Revoked) {
                    $connection->forceFill([
                        'status' => ShopifyConnectionStatus::Active,
                        'sync_status' => 'idle',
                        'last_synced_at' => now(),
                        'bulk_operation_id' => null,
                        'bulk_operation_url' => null,
                        'last_sync_error' => null,
                    ])->save();

                    InvalidateShopifyProductSearchCacheAction::run($connection);
                }

                return count($products);
            } catch (Throwable $throwable) {
                $connection->refresh();

                if ($connection->status !== ShopifyConnectionStatus::Revoked) {
                    $connection->forceFill([
                        'sync_status' => 'failed',
                        'status' => ShopifyConnectionStatus::Error,
                        'last_sync_error' => $throwable->getMessage(),
                    ])->save();
                }

                throw $throwable;
            }
        });
    }

    /**
     * @return array<int, ShopifyProductData>
     */
    private function downloadProducts(string $url): array
    {
        $response = Http::get($url);

        throw_unless($response->successful(), RuntimeException::class, 'Shopify bulk operation download failed.');

        $products = [];
        $lines = preg_split('/\r\n|\r|\n/', $response->body()) ?: [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $node = json_decode($line, true);
            if (! is_array($node)) {
                continue;
            }

            if (! is_string($node['id'] ?? null)) {
                continue;
            }

            $products[] = $this->mapProductNode($node);
        }

        return $products;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function mapProductNode(array $node): ShopifyProductData
    {
        $optionNodes = is_array($node['options'] ?? null) ? $node['options'] : [];
        $variantEdges = is_array(data_get($node, 'variants.edges')) ? data_get($node, 'variants.edges') : [];

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

        $variants = collect($variantEdges)
            ->map(static fn (mixed $edge): mixed => is_array($edge) ? ($edge['node'] ?? null) : null)
            ->filter(static fn (mixed $variant): bool => is_array($variant) && is_string($variant['id'] ?? null))
            ->map(fn (array $variant): ShopifyProductVariantData => $this->mapVariantNode($variant))
            ->values()
            ->all();

        $featuredImage = is_array($node['featuredImage'] ?? null) ? $node['featuredImage'] : null;

        return new ShopifyProductData(
            shopifyGid: (string) $node['id'],
            handle: (string) ($node['handle'] ?? ''),
            title: (string) ($node['title'] ?? ''),
            status: mb_strtolower((string) ($node['status'] ?? 'unknown')),
            options: $options,
            featuredImage: $featuredImage,
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

    private function persistProduct(ShopifyConnection $connection, ShopifyProductData $product): void
    {
        /** @var ShopifyProduct $model */
        $model = ShopifyProduct::query()->updateOrCreate(
            [
                'connection_id' => $connection->getKey(),
                'shopify_gid' => $product->shopifyGid,
            ],
            [
                'handle' => $product->handle,
                'title' => $product->title,
                'search_text' => ShopifyProduct::searchableText($product->title, $product->handle),
                'status' => $product->status,
                'options' => $product->options,
                'featured_image' => $product->featuredImage,
                'raw_snapshot' => $product->rawSnapshot,
                'synced_at' => now(),
            ],
        );

        $seenVariantGids = [];

        foreach ($product->variants as $variant) {
            $seenVariantGids[] = $variant->shopifyGid;

            $model->variants()->updateOrCreate(
                ['shopify_gid' => $variant->shopifyGid],
                [
                    'title' => $variant->title,
                    'price_amount' => $variant->priceAmount,
                    'price_currency' => $variant->priceCurrency,
                    'available_for_sale' => $variant->availableForSale,
                    'selected_options' => $variant->selectedOptions,
                ],
            );
        }

        if ($seenVariantGids === []) {
            $model->variants()->delete();

            return;
        }

        $model->variants()->whereNotIn('shopify_gid', $seenVariantGids)->delete();
    }
}
