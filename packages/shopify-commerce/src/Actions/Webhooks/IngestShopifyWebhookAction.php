<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Webhooks;

use Capell\ShopifyCommerce\Actions\Catalog\InvalidateShopifyProductSearchCacheAction;
use Capell\ShopifyCommerce\Actions\Catalog\PersistShopifyProductAction;
use Capell\ShopifyCommerce\Actions\Customers\UpsertShopifyCustomerAction;
use Capell\ShopifyCommerce\Actions\OAuth\DisconnectShopifyStoreAction;
use Capell\ShopifyCommerce\Data\ShopifyProductData;
use Capell\ShopifyCommerce\Data\ShopifyProductOptionData;
use Capell\ShopifyCommerce\Data\ShopifyProductVariantData;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class IngestShopifyWebhookAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(ShopifyConnection $connection, string $topic, array $payload): void
    {
        match ($topic) {
            'products/create', 'products/update' => $this->upsertProduct($connection, $payload),
            'products/delete' => $this->deleteProduct($connection, $payload),
            'customers/create', 'customers/update' => UpsertShopifyCustomerAction::run($connection, $payload),
            'app/uninstalled' => DisconnectShopifyStoreAction::run($connection),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertProduct(ShopifyConnection $connection, array $payload): void
    {
        $product = $this->productData($payload);

        if (! $product instanceof ShopifyProductData) {
            return;
        }

        PersistShopifyProductAction::run($connection, $product, now());
        InvalidateShopifyProductSearchCacheAction::run($connection);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function deleteProduct(ShopifyConnection $connection, array $payload): void
    {
        $productGid = $this->gid('Product', $payload['admin_graphql_api_id'] ?? $payload['id'] ?? null);

        if ($productGid === null) {
            return;
        }

        ShopifyProduct::query()
            ->where('connection_id', $connection->getKey())
            ->where('shopify_gid', $productGid)
            ->delete();

        InvalidateShopifyProductSearchCacheAction::run($connection);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function productData(array $payload): ?ShopifyProductData
    {
        $productId = $this->gid('Product', $payload['admin_graphql_api_id'] ?? $payload['id'] ?? null);

        if ($productId === null) {
            return null;
        }

        $options = $this->options($payload);
        $variants = $this->variants($payload);

        return new ShopifyProductData(
            shopifyGid: $productId,
            handle: $this->stringValue($payload['handle'] ?? ''),
            title: $this->stringValue($payload['title'] ?? ''),
            status: mb_strtolower($this->stringValue($payload['status'] ?? 'unknown', 'unknown')),
            options: $options,
            featuredImage: $this->featuredImage($payload),
            variants: $variants,
            rawSnapshot: $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<ShopifyProductOptionData>
     */
    private function options(array $payload): array
    {
        $options = is_array($payload['options'] ?? null) ? $payload['options'] : [];

        return array_values(collect($options)
            ->filter(static fn (mixed $option): bool => is_array($option))
            ->map(fn (array $option): ShopifyProductOptionData => new ShopifyProductOptionData(
                name: $this->stringValue($option['name'] ?? ''),
                values: array_values(array_filter(
                    is_array($option['values'] ?? null) ? $option['values'] : [],
                    static fn (mixed $value): bool => is_string($value) && $value !== '',
                )),
            ))
            ->all());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<ShopifyProductVariantData>
     */
    private function variants(array $payload): array
    {
        $variants = is_array($payload['variants'] ?? null) ? $payload['variants'] : [];

        return array_values(collect($variants)
            ->filter(static fn (mixed $variant): bool => is_array($variant))
            ->map(fn (array $variant): ShopifyProductVariantData => $this->variantData($this->stringKeyedArray($variant)))
            ->all());
    }

    /**
     * @param  array<string, mixed>  $variant
     */
    private function variantData(array $variant): ShopifyProductVariantData
    {
        $selectedOptions = [];

        foreach (['option1', 'option2', 'option3'] as $optionKey) {
            if (! is_string($variant[$optionKey] ?? null)) {
                continue;
            }

            if ($variant[$optionKey] === '') {
                continue;
            }

            $selectedOptions[] = new ShopifyProductOptionData(
                name: Str::headline($optionKey),
                value: $variant[$optionKey],
            );
        }

        return new ShopifyProductVariantData(
            shopifyGid: $this->gid('ProductVariant', $variant['admin_graphql_api_id'] ?? $variant['id'] ?? null) ?? '',
            title: $this->stringValue($variant['title'] ?? ''),
            priceAmount: $this->stringValue($variant['price'] ?? '0', '0'),
            priceCurrency: $this->stringValue($variant['currency'] ?? config('capell-shopify-commerce.default_currency', 'USD'), 'USD'),
            availableForSale: $this->integerValue($variant['inventory_quantity'] ?? 0) > 0,
            selectedOptions: $selectedOptions,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function featuredImage(array $payload): ?array
    {
        $image = $payload['image'] ?? $payload['featured_image'] ?? null;

        return is_array($image) ? $this->stringKeyedArray($image) : null;
    }

    private function gid(string $resource, mixed $value): ?string
    {
        if (is_string($value) && str_starts_with($value, 'gid://shopify/')) {
            return $value;
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return sprintf('gid://shopify/%s/%s', $resource, $value);
        }

        return null;
    }

    private function integerValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
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
