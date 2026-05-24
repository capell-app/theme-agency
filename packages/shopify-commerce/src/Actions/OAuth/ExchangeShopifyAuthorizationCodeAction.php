<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Capell\ShopifyCommerce\Data\ShopifyTokenExchangeResponseData;
use Capell\ShopifyCommerce\Exceptions\ShopifyOAuthException;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

final class ExchangeShopifyAuthorizationCodeAction
{
    use AsAction;

    public function handle(string $shopDomain, string $code): ShopifyTokenExchangeResponseData
    {
        $response = Http::asForm()
            ->post(sprintf('https://%s/admin/oauth/access_token', $shopDomain), [
                'client_id' => config('capell-shopify-commerce.client_id'),
                'client_secret' => config('capell-shopify-commerce.client_secret'),
                'code' => $code,
            ]);

        throw_unless($response->successful(), ShopifyOAuthException::class, 'Shopify token exchange failed.');

        $payload = $response->json();
        $accessToken = is_array($payload) ? ($payload['access_token'] ?? null) : null;

        throw_if(! is_string($accessToken) || $accessToken === '', ShopifyOAuthException::class, 'Shopify token exchange did not return an access token.');

        $scopeValue = is_array($payload) ? ($payload['scope'] ?? '') : '';
        $scopes = is_string($scopeValue)
            ? array_values(array_filter(array_map(trim(...), explode(',', $scopeValue)), static fn (string $scope): bool => $scope !== ''))
            : [];

        return new ShopifyTokenExchangeResponseData(
            accessToken: $accessToken,
            scopes: $scopes,
        );
    }
}
