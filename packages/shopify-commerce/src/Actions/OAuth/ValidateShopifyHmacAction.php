<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Lorisleiva\Actions\Concerns\AsAction;

final class ValidateShopifyHmacAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $queryParams
     */
    public function handle(array $queryParams, mixed $clientSecret): bool
    {
        if (! is_string($clientSecret) || $clientSecret === '') {
            return false;
        }

        $providedHmac = $queryParams['hmac'] ?? null;

        if (! is_string($providedHmac) || $providedHmac === '') {
            return false;
        }

        unset($queryParams['hmac'], $queryParams['signature']);
        ksort($queryParams);

        $message = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        $expectedHmac = hash_hmac('sha256', $message, $clientSecret);

        return hash_equals($expectedHmac, $providedHmac);
    }
}
