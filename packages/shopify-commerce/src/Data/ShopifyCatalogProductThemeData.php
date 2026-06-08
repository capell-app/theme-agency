<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Spatie\LaravelData\Data;

final class ShopifyCatalogProductThemeData extends Data
{
    /**
     * @param  array{url: string, altText: string}|null  $featuredImage
     * @param  list<ShopifyCatalogVariantThemeData>  $variants
     */
    public function __construct(
        public string $title,
        public string $handle,
        public string $url,
        public ?array $featuredImage,
        public bool $availableForSale,
        public ?string $priceAmount,
        public ?string $priceCurrency,
        public ?string $presentmentCurrency,
        public ?string $syncedAt,
        public array $variants = [],
    ) {}

    /**
     * @return array{title: string, handle: string, url: string, featuredImage?: array{url: string, altText: string}, availableForSale: bool, priceAmount: string|null, priceCurrency: string|null, presentmentCurrency: string|null, syncedAt: string|null, variants: list<array{title: string, priceAmount: string, priceCurrency: string, presentmentCurrency: string, availableForSale: bool, selectedOptions: list<array{name: string, value: string|null}>}>}
     */
    public function toThemeArray(): array
    {
        $payload = [
            'title' => $this->title,
            'handle' => $this->handle,
            'url' => $this->url,
            'availableForSale' => $this->availableForSale,
            'priceAmount' => $this->priceAmount,
            'priceCurrency' => $this->priceCurrency,
            'presentmentCurrency' => $this->presentmentCurrency,
            'syncedAt' => $this->syncedAt,
            'variants' => array_map(
                static fn (ShopifyCatalogVariantThemeData $variant): array => $variant->toThemeArray(),
                $this->variants,
            ),
        ];

        if ($this->featuredImage !== null) {
            $payload['featuredImage'] = $this->featuredImage;
        }

        return $payload;
    }
}
