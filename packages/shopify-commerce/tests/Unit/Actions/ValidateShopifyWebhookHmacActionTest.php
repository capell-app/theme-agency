<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Webhooks\ValidateShopifyWebhookHmacAction;

it('validates shopify webhook hmac headers', function (): void {
    $payload = json_encode(['id' => 123], JSON_THROW_ON_ERROR);
    $hmac = base64_encode(hash_hmac('sha256', $payload, 'client-secret', binary: true));

    expect(ValidateShopifyWebhookHmacAction::run($payload, $hmac, 'client-secret'))->toBeTrue()
        ->and(ValidateShopifyWebhookHmacAction::run($payload, 'bad-signature', 'client-secret'))->toBeFalse()
        ->and(ValidateShopifyWebhookHmacAction::run($payload, $hmac, null))->toBeFalse();
});
