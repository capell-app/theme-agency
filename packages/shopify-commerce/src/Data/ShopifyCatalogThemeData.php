<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Spatie\LaravelData\Data;

final class ShopifyCatalogThemeData extends Data
{
    /**
     * @param  list<ShopifyCatalogProductThemeData>  $products
     */
    public function __construct(
        public array $products,
        public ShopifyCatalogSummaryThemeData $summary,
    ) {}

    public static function blank(): self
    {
        return new self(
            products: [],
            summary: ShopifyCatalogSummaryThemeData::blank(),
        );
    }

    /**
     * @return array{items: list<array<string, mixed>>, shopifySummary: array<string, mixed>}
     */
    public function toThemeArray(): array
    {
        return [
            'items' => array_map(
                static fn (ShopifyCatalogProductThemeData $product): array => $product->toThemeArray(),
                $this->products,
            ),
            'shopifySummary' => $this->summary->toThemeArray(),
        ];
    }
}
