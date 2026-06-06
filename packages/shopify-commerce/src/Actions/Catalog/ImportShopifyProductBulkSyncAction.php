<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Data\ShopifyProductData;
use Capell\ShopifyCommerce\Data\ShopifyProductOptionData;
use Capell\ShopifyCommerce\Data\ShopifyProductVariantData;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Enums\ShopifySyncStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use SplFileObject;
use Throwable;

/**
 * @method static int run(ShopifyConnection|int $connection)
 */
final class ImportShopifyProductBulkSyncAction
{
    use AsAction;

    public function handle(ShopifyConnection|int $connection): int
    {
        $connection = is_int($connection) ? ShopifyConnection::query()->findOrFail($connection) : $connection;

        $imported = Cache::lock(sprintf('capell-shopify-commerce.sync.%d', $this->intValue($connection->getKey())), 300)->block(10, function () use ($connection): int {
            $connection->refresh();

            if ($connection->status === ShopifyConnectionStatus::Revoked) {
                return 0;
            }

            $bulkOperationUrl = $connection->bulk_operation_url;
            throw_if(! is_string($bulkOperationUrl) || $bulkOperationUrl === '', RuntimeException::class, 'Shopify bulk operation URL is missing.');

            try {
                $connection->forceFill(['sync_status' => ShopifySyncStatus::Importing->value])->save();

                $products = $this->downloadProducts($bulkOperationUrl);
                $syncedAt = now();
                $imported = 0;

                DB::transaction(function () use ($connection, $products, $syncedAt, &$imported): void {
                    foreach ($products as $product) {
                        $this->persistProduct($connection, $product, $syncedAt);
                        $imported++;
                    }

                    ShopifyProduct::query()
                        ->where('connection_id', $connection->getKey())
                        ->where(function (Builder $query) use ($syncedAt): void {
                            $query
                                ->whereNull('synced_at')
                                ->orWhere('synced_at', '<', $syncedAt);
                        })
                        ->delete();
                });

                $connection->refresh();

                if ($connection->status !== ShopifyConnectionStatus::Revoked) {
                    $connection->forceFill([
                        'status' => ShopifyConnectionStatus::Active,
                        'sync_status' => ShopifySyncStatus::Idle->value,
                        'last_synced_at' => now(),
                        'bulk_operation_id' => null,
                        'bulk_operation_url' => null,
                        'last_sync_error' => null,
                    ])->save();

                    InvalidateShopifyProductSearchCacheAction::run($connection);
                }

                return $imported;
            } catch (Throwable $throwable) {
                $connection->refresh();

                if ($connection->status !== ShopifyConnectionStatus::Revoked) {
                    $connection->forceFill([
                        'sync_status' => ShopifySyncStatus::Failed->value,
                        'status' => ShopifyConnectionStatus::Error,
                        'last_sync_error' => SanitizeShopifySyncErrorAction::run($throwable, $connection),
                    ])->save();
                }

                throw $throwable;
            }
        });

        return is_int($imported) ? $imported : 0;
    }

    private function intValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @return iterable<int, ShopifyProductData>
     */
    private function downloadProducts(string $url): iterable
    {
        $path = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'capell-shopify-bulk-' . Str::uuid()->toString();

        try {
            $response = Http::timeout($this->httpTimeout())
                ->sink($path)
                ->get($url);

            throw_unless($response->successful(), RuntimeException::class, 'Shopify bulk operation download failed.');

            if (filesize($path) === 0 && $response->body() !== '') {
                file_put_contents($path, $response->body());
            }

            $file = new SplFileObject($path, 'r');

            while (! $file->eof()) {
                $line = $file->fgets();

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

                yield $this->mapProductNode($node);
            }
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function httpTimeout(): int
    {
        return max(1, (int) config('capell-shopify-commerce.http_timeout', 15));
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

    private function persistProduct(ShopifyConnection $connection, ShopifyProductData $product, CarbonInterface $syncedAt): void
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
                'synced_at' => $syncedAt,
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
