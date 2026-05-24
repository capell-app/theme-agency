<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Spatie\LaravelData\Data;

final class ShopifyTokenExchangeResponseData extends Data
{
    /**
     * @param  array<int, string>  $scopes
     */
    public function __construct(
        public string $accessToken,
        public array $scopes,
    ) {}
}
