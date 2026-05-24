<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Exceptions;

use RuntimeException;

final class ShopifyGraphqlException extends RuntimeException
{
    /**
     * @param  array<int, mixed>  $errors
     */
    public function __construct(public readonly array $errors = [])
    {
        parent::__construct('Shopify Admin API request failed.');
    }
}
