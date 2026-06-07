<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Graphql;

use Capell\ShopifyCommerce\Exceptions\ShopifyGraphqlException;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<string, mixed> run(ShopifyConnection $connection, string $query, array<string, mixed> $variables = [])
 */
final class ExecuteShopifyAdminGraphqlAction
{
    use AsAction;

    private const int MAX_ATTEMPTS = 3;

    /**
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    public function handle(ShopifyConnection $connection, string $query, array $variables = []): array
    {
        $apiVersion = $this->apiVersion();
        $response = $this->postWithThrottleRetries($connection, $apiVersion, $query, $variables);

        throw_unless($response->successful(), ShopifyGraphqlException::class);

        $payload = $response->json();

        throw_unless(is_array($payload), ShopifyGraphqlException::class);

        $errors = $payload['errors'] ?? null;

        throw_if(is_array($errors) && $errors !== [], ShopifyGraphqlException::class, $errors);

        $this->paceForThrottleStatus($connection, $payload);

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function postWithThrottleRetries(ShopifyConnection $connection, string $apiVersion, string $query, array $variables): Response
    {
        $endpoint = sprintf('https://%s/admin/api/%s/graphql.json', $connection->shop_domain, $apiVersion);

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $this->paceFromCachedThrottleState($connection);

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => (string) $connection->access_token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout($this->httpTimeout())
                ->post($endpoint, [
                    'query' => $query,
                    'variables' => $variables,
                ]);

            if ($response->status() !== 429 || $attempt === self::MAX_ATTEMPTS) {
                return $response;
            }

            $this->sleepForRetryAfter($response);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function paceForThrottleStatus(ShopifyConnection $connection, array $payload): void
    {
        $sleepMicroseconds = $this->throttleDelayMicroseconds($payload);

        if ($sleepMicroseconds <= 0) {
            return;
        }

        $this->rememberThrottleDelay($connection, $sleepMicroseconds);

        Sleep::usleep($sleepMicroseconds);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function throttleDelayMicroseconds(array $payload): int
    {
        $requestedQueryCost = data_get($payload, 'extensions.cost.requestedQueryCost');
        $currentlyAvailable = data_get($payload, 'extensions.cost.throttleStatus.currentlyAvailable');
        $restoreRate = data_get($payload, 'extensions.cost.throttleStatus.restoreRate');

        if (! is_numeric($requestedQueryCost) || ! is_numeric($currentlyAvailable) || ! is_numeric($restoreRate)) {
            return 0;
        }

        $deficit = (int) ceil((float) $requestedQueryCost - (float) $currentlyAvailable);

        if ($deficit <= 0) {
            return 0;
        }

        $restoreRatePerSecond = max(1.0, (float) $restoreRate);

        return min(5_000_000, (int) ceil(($deficit / $restoreRatePerSecond) * 1_000_000));
    }

    private function paceFromCachedThrottleState(ShopifyConnection $connection): void
    {
        $availableAt = Cache::get($this->throttleCacheKey($connection));

        if (! is_numeric($availableAt)) {
            return;
        }

        $sleepMicroseconds = (int) ceil(((float) $availableAt - microtime(true)) * 1_000_000);

        if ($sleepMicroseconds > 0) {
            Sleep::usleep(min(5_000_000, $sleepMicroseconds));
        }
    }

    private function rememberThrottleDelay(ShopifyConnection $connection, int $sleepMicroseconds): void
    {
        Cache::put(
            $this->throttleCacheKey($connection),
            microtime(true) + ($sleepMicroseconds / 1_000_000),
            now()->addMinute(),
        );
    }

    private function sleepForRetryAfter(Response $response): void
    {
        $sleepSeconds = $this->retryAfterSeconds($response->header('Retry-After'));

        if ($sleepSeconds > 0) {
            Sleep::sleep(min(60, $sleepSeconds));
        }
    }

    private function retryAfterSeconds(?string $header): int
    {
        if ($header === null || trim($header) === '') {
            return 1;
        }

        if (ctype_digit($header)) {
            return max(0, (int) $header);
        }

        $timestamp = strtotime($header);

        if ($timestamp === false) {
            return 1;
        }

        return max(0, $timestamp - time());
    }

    private function throttleCacheKey(ShopifyConnection $connection): string
    {
        return sprintf('capell-shopify-commerce.graphql.throttle.%s', $connection->getKey());
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
