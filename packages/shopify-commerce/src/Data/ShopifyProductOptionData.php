<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Spatie\LaravelData\Data;

final class ShopifyProductOptionData extends Data
{
    /**
     * @param  array<int, string>  $values
     */
    public function __construct(
        public string $name,
        public array $values = [],
        public ?string $value = null,
    ) {}
}
