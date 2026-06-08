<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Data\ShopifyCatalogProductThemeData;
use Capell\ShopifyCommerce\Data\ShopifyCatalogSummaryThemeData;
use Capell\ShopifyCommerce\Data\ShopifyCatalogThemeData;
use Capell\ShopifyCommerce\Data\ShopifyCatalogVariantThemeData;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyProductVariant;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\DataCollection;

/**
 * Builds a safe, local-cache-only catalog payload for hydrated theme renderers.
 *
 * @method static ShopifyCatalogThemeData run(ShopifyConnection $connection, int $limit = 12, ?string $searchTerm = null, array $handles = [], bool $includeInactive = false)
 */
final class BuildShopifyCatalogThemeDataAction
{
    use AsAction;

    /**
     * @param  array<array-key, mixed>  $handles
     */
    public function handle(
        ShopifyConnection $connection,
        int $limit = 12,
        ?string $searchTerm = null,
        array $handles = [],
        bool $includeInactive = false,
    ): ShopifyCatalogThemeData {
        if (! $connection->isActive()) {
            return ShopifyCatalogThemeData::blank();
        }

        $limit = max(1, min(50, $limit));
        $searchTerm = mb_strtolower(trim((string) $searchTerm));
        $handles = $this->normalizeHandles($handles);

        $products = $this->products($connection, $limit, $searchTerm, $handles, $includeInactive);
        $productData = $products
            ->map(fn (ShopifyProduct $product): ShopifyCatalogProductThemeData => $this->productData($product))
            ->values()
            ->all();
        $summaryData = $this->summaryData($connection, $includeInactive);

        return new ShopifyCatalogThemeData(
            products: $productData,
            summary: $summaryData,
        );
    }

    /**
     * @param  list<string>  $handles
     * @return EloquentCollection<int, ShopifyProduct>
     */
    private function products(
        ShopifyConnection $connection,
        int $limit,
        string $searchTerm,
        array $handles,
        bool $includeInactive,
    ): EloquentCollection {
        return ShopifyProduct::query()
            ->with(['variants' => static function (HasMany $query): void {
                $query->orderBy('id');
            }])
            ->where('connection_id', $connection->getKey())
            ->when(! $includeInactive, static function (Builder $query): void {
                $query->where('status', 'active');
            })
            ->when($handles !== [], static function (Builder $query) use ($handles): void {
                $query->whereIn('handle', $handles);
            })
            ->when($searchTerm !== '', static function (Builder $query) use ($searchTerm): void {
                $query->where(function (Builder $nestedQuery) use ($searchTerm): void {
                    $nestedQuery
                        ->where('search_text', 'like', '%' . $searchTerm . '%')
                        ->orWhere('title', 'like', '%' . $searchTerm . '%')
                        ->orWhere('handle', 'like', '%' . $searchTerm . '%');
                });
            })
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    private function productData(ShopifyProduct $product): ShopifyCatalogProductThemeData
    {
        $variants = $product->variants
            ->map(fn (ShopifyProductVariant $variant): ShopifyCatalogVariantThemeData => $this->variantData($variant))
            ->values()
            ->all();

        $firstVariant = $variants[0] ?? null;
        $availableForSale = collect($variants)
            ->contains(static fn (ShopifyCatalogVariantThemeData $variant): bool => $variant->availableForSale);

        return new ShopifyCatalogProductThemeData(
            title: $product->title,
            handle: $product->handle,
            url: '/products/' . ltrim($product->handle, '/'),
            featuredImage: $this->featuredImage($product->featured_image),
            availableForSale: $availableForSale,
            priceAmount: $firstVariant?->priceAmount,
            priceCurrency: $firstVariant?->priceCurrency,
            presentmentCurrency: $firstVariant?->presentmentCurrency,
            syncedAt: $this->formatDate($product->synced_at),
            variants: $variants,
        );
    }

    private function variantData(ShopifyProductVariant $variant): ShopifyCatalogVariantThemeData
    {
        $currency = $variant->price_currency;

        return new ShopifyCatalogVariantThemeData(
            title: $variant->title,
            priceAmount: $this->priceAmount($variant),
            priceCurrency: $currency,
            presentmentCurrency: $currency,
            availableForSale: (bool) $variant->available_for_sale,
            selectedOptions: $this->selectedOptions($variant->selected_options),
        );
    }

    private function summaryData(ShopifyConnection $connection, bool $includeInactive): ShopifyCatalogSummaryThemeData
    {
        $productQuery = ShopifyProduct::query()
            ->where('connection_id', $connection->getKey())
            ->when(! $includeInactive, static function (Builder $query): void {
                $query->where('status', 'active');
            });

        $variantQuery = ShopifyProductVariant::query()
            ->whereHas('product', function (Builder $query) use ($connection, $includeInactive): void {
                $query
                    ->where('connection_id', $connection->getKey())
                    ->when(! $includeInactive, static function (Builder $nestedQuery): void {
                        $nestedQuery->where('status', 'active');
                    });
            });

        $presentmentCurrencies = (clone $variantQuery)
            ->distinct()
            ->orderBy('price_currency')
            ->pluck('price_currency')
            ->filter(static fn (mixed $currency): bool => is_string($currency) && $currency !== '')
            ->values()
            ->all();

        $latestSyncedProduct = (clone $productQuery)
            ->orderByDesc('synced_at')
            ->first();
        $productsSynced = (clone $productQuery)->count();
        $variantsSynced = (clone $variantQuery)->count();
        $availableStock = (clone $variantQuery)->where('available_for_sale', true)->count();

        return new ShopifyCatalogSummaryThemeData(
            productsSynced: $productsSynced,
            variantsSynced: $variantsSynced,
            availableStock: $availableStock,
            presentmentCurrency: $presentmentCurrencies[0] ?? null,
            presentmentCurrencies: $presentmentCurrencies,
            syncedAt: $latestSyncedProduct instanceof ShopifyProduct ? $this->formatDate($latestSyncedProduct->synced_at) : null,
        );
    }

    /**
     * @param  array<array-key, mixed>  $handles
     * @return list<string>
     */
    private function normalizeHandles(array $handles): array
    {
        return array_values(array_unique(array_filter(
            array_map(static fn (mixed $handle): string => is_string($handle) ? trim($handle) : '', $handles),
            static fn (string $handle): bool => $handle !== '',
        )));
    }

    private function priceAmount(ShopifyProductVariant $variant): string
    {
        $amount = (string) $variant->getAttribute('price_amount');

        if (! is_numeric($amount)) {
            return $amount;
        }

        $trimmed = rtrim(rtrim($amount, '0'), '.');

        if ($trimmed === '') {
            return '0.00';
        }

        return str_contains($trimmed, '.') ? $trimmed : $trimmed . '.00';
    }

    /**
     * @param  array<string, mixed>|null  $image
     * @return array{url: string, altText: string}|null
     */
    private function featuredImage(?array $image): ?array
    {
        if (! is_string($image['url'] ?? null) || $image['url'] === '') {
            return null;
        }

        return [
            'url' => $image['url'],
            'altText' => is_string($image['altText'] ?? null) ? $image['altText'] : '',
        ];
    }

    /**
     * @return list<array{name: string, value: string|null}>
     */
    private function selectedOptions(mixed $selectedOptions): array
    {
        $rows = $selectedOptions instanceof DataCollection
            ? $selectedOptions->toArray()
            : (is_array($selectedOptions) ? $selectedOptions : []);

        return collect($rows)
            ->filter(static fn (mixed $option): bool => is_array($option))
            ->map(static fn (array $option): array => [
                'name' => (string) ($option['name'] ?? ''),
                'value' => is_string($option['value'] ?? null) ? $option['value'] : null,
            ])
            ->filter(static fn (array $option): bool => $option['name'] !== '' || $option['value'] !== null)
            ->values()
            ->all();
    }

    private function formatDate(mixed $date): ?string
    {
        return $date instanceof CarbonInterface ? $date->toIso8601String() : null;
    }
}
