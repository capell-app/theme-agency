<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Lorisleiva\Actions\Concerns\AsAction;

final class BuildShopifyAuthorizeUrlAction
{
    use AsAction;

    /**
     * @param  array<int, string>  $scopes
     */
    public function handle(string $shopDomain, array $scopes, string $state, string $redirectUri): string
    {
        return sprintf('https://%s/admin/oauth/authorize?%s', $shopDomain, http_build_query([
            'client_id' => (string) config('capell-shopify-commerce.client_id', ''),
            'scope' => implode(',', array_values($scopes)),
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986));
    }
}
