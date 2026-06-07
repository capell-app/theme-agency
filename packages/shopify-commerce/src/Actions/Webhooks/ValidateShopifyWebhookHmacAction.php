<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Webhooks;

use Lorisleiva\Actions\Concerns\AsAction;

final class ValidateShopifyWebhookHmacAction
{
    use AsAction;

    public function handle(string $payload, mixed $providedHmac, mixed $clientSecret): bool
    {
        if (! is_string($providedHmac) || $providedHmac === '') {
            return false;
        }

        if (! is_string($clientSecret) || $clientSecret === '') {
            return false;
        }

        $expectedHmac = base64_encode(hash_hmac('sha256', $payload, $clientSecret, binary: true));

        return hash_equals($expectedHmac, $providedHmac);
    }
}
