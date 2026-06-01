<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Graphql;

use Capell\ShopifyCommerce\Exceptions\ShopifyGraphqlException;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Lorisleiva\Actions\Concerns\AsAction;

final class ExecuteShopifyAdminGraphqlAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    public function handle(ShopifyConnection $connection, string $query, array $variables = []): array
    {
        $apiVersion = $this->apiVersion();
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => (string) $connection->access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->retry(2, 250, throw: false)
            ->timeout($this->httpTimeout())
            ->post(sprintf('https://%s/admin/api/%s/graphql.json', $connection->shop_domain, $apiVersion), [
                'query' => $query,
                'variables' => $variables,
            ]);

        throw_unless($response->successful(), ShopifyGraphqlException::class);

        $payload = $response->json();

        throw_unless(is_array($payload), ShopifyGraphqlException::class);

        $errors = $payload['errors'] ?? null;

        throw_if(is_array($errors) && $errors !== [], ShopifyGraphqlException::class, $errors);

        $this->paceForThrottleStatus($payload);

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function paceForThrottleStatus(array $payload): void
    {
        $requestedQueryCost = data_get($payload, 'extensions.cost.requestedQueryCost');
        $currentlyAvailable = data_get($payload, 'extensions.cost.throttleStatus.currentlyAvailable');
        $restoreRate = data_get($payload, 'extensions.cost.throttleStatus.restoreRate');

        if (! is_numeric($requestedQueryCost) || ! is_numeric($currentlyAvailable) || ! is_numeric($restoreRate)) {
            return;
        }

        $deficit = (int) ceil((float) $requestedQueryCost - (float) $currentlyAvailable);

        if ($deficit <= 0) {
            return;
        }

        $restoreRatePerSecond = max(1.0, (float) $restoreRate);
        $sleepMicroseconds = min(5_000_000, (int) ceil(($deficit / $restoreRatePerSecond) * 1_000_000));

        Sleep::usleep($sleepMicroseconds);
    }

    private function apiVersion(): string
    {
        if (app()->bound(ShopifyCommerceSettings::class)) {
            $settings = resolve(ShopifyCommerceSettings::class);

            if ($settings->api_version !== '') {
                return $settings->api_version;
            }
        }

        return (string) config('capell-shopify-commerce.default_api_version', '2026-04');
    }

    private function httpTimeout(): int
    {
        return max(1, (int) config('capell-shopify-commerce.http_timeout', 15));
    }
}
