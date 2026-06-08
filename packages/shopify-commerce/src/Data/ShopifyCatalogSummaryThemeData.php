<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Spatie\LaravelData\Data;

final class ShopifyCatalogSummaryThemeData extends Data
{
    /**
     * @param  list<string>  $presentmentCurrencies
     */
    public function __construct(
        public int $productsSynced,
        public int $variantsSynced,
        public int $availableStock,
        public ?string $presentmentCurrency,
        public array $presentmentCurrencies,
        public ?string $syncedAt,
    ) {}

    public static function blank(): self
    {
        return new self(
            productsSynced: 0,
            variantsSynced: 0,
            availableStock: 0,
            presentmentCurrency: null,
            presentmentCurrencies: [],
            syncedAt: null,
        );
    }

    /**
     * @return array{productsSynced: int, variantsSynced: int, availableStock: int, presentmentCurrency: string|null, presentmentCurrencies: list<string>, syncedAt: string|null}
     */
    public function toThemeArray(): array
    {
        return [
            'productsSynced' => $this->productsSynced,
            'variantsSynced' => $this->variantsSynced,
            'availableStock' => $this->availableStock,
            'presentmentCurrency' => $this->presentmentCurrency,
            'presentmentCurrencies' => $this->presentmentCurrencies,
            'syncedAt' => $this->syncedAt,
        ];
    }
}
