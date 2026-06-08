<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Spatie\LaravelData\Data;

final class ShopifyCatalogVariantThemeData extends Data
{
    /**
     * @param  list<array{name: string, value: string|null}>  $selectedOptions
     */
    public function __construct(
        public string $title,
        public string $priceAmount,
        public string $priceCurrency,
        public string $presentmentCurrency,
        public bool $availableForSale,
        public array $selectedOptions = [],
    ) {}

    /**
     * @return array{title: string, priceAmount: string, priceCurrency: string, presentmentCurrency: string, availableForSale: bool, selectedOptions: list<array{name: string, value: string|null}>}
     */
    public function toThemeArray(): array
    {
        return [
            'title' => $this->title,
            'priceAmount' => $this->priceAmount,
            'priceCurrency' => $this->priceCurrency,
            'presentmentCurrency' => $this->presentmentCurrency,
            'availableForSale' => $this->availableForSale,
            'selectedOptions' => $this->selectedOptions,
        ];
    }
}
