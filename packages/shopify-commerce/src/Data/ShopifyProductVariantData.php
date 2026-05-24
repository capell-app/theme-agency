<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Spatie\LaravelData\Data;

final class ShopifyProductVariantData extends Data
{
    /**
     * @param  array<int, ShopifyProductOptionData>  $selectedOptions
     */
    public function __construct(
        public string $shopifyGid,
        public string $title,
        public string $priceAmount,
        public string $priceCurrency,
        public bool $availableForSale,
        public array $selectedOptions = [],
    ) {}
}
