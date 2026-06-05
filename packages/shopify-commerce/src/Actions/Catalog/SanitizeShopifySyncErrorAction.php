<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Lorisleiva\Actions\Concerns\AsObject;
use Throwable;

final class SanitizeShopifySyncErrorAction
{
    use AsObject;

    public function handle(Throwable|string $error, ?ShopifyConnection $connection = null): string
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;
        $message = trim(strip_tags($message));

        if ($message === '') {
            return 'Shopify sync failed.';
        }

        $message = preg_replace('/https?:\/\/[^\s)>\"]+/i', '[shopify-url]', $message) ?? $message;
        $message = preg_replace('/\b(shpat|shpca|shppa|shpss)_[A-Za-z0-9_\-]+/i', '[shopify-token]', $message) ?? $message;
        $message = preg_replace('/\b(Authorization)(\s*[:=]\s*)(Bearer|Basic)\s+[^\s,;]+/i', '$1$2$3 [redacted]', $message) ?? $message;
        $message = preg_replace('/\b(X-Shopify-Access-Token|access_token|client_secret|token)(\s*[=:]\s*)[^\s,;]+/i', '$1$2[redacted]', $message) ?? $message;

        $shopDomain = $connection?->shop_domain;
        if (is_string($shopDomain) && $shopDomain !== '') {
            $message = str_replace($shopDomain, '[shopify-shop]', $message);
        }

        $accessToken = $connection?->access_token;
        if (is_string($accessToken) && $accessToken !== '') {
            $message = str_replace($accessToken, '[shopify-token]', $message);
        }

        return mb_substr($message, 0, 1000);
    }
}
