<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Spatie\LaravelData\Data;

final class ShopifyProductData extends Data
{
    /**
     * @param  array<int, ShopifyProductOptionData>  $options
     * @param  array<string, mixed>|null  $featuredImage
     * @param  array<int, ShopifyProductVariantData>  $variants
     * @param  array<string, mixed>  $rawSnapshot
     */
    public function __construct(
        public string $shopifyGid,
        public string $handle,
        public string $title,
        public string $status,
        public array $options = [],
        public ?array $featuredImage = null,
        public array $variants = [],
        public array $rawSnapshot = [],
    ) {}
}
