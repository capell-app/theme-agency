<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Data;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Override;
use Spatie\LaravelData\Data;

final class ShopifyCallbackQueryData extends Data
{
    public function __construct(
        public string $code,
        public string $hmac,
        public string $shop,
        public string $state,
        public ?string $host,
        public ?string $timestamp,
    ) {}

    #[Override]
    public static function from(mixed ...$payloads): static
    {
        $payload = $payloads[0] ?? [];

        if ($payload instanceof Request) {
            $payload = $payload->query();
        }

        throw_unless(is_array($payload), InvalidArgumentException::class, 'Shopify callback query payload must be an array or request.');

        $code = $payload['code'] ?? null;
        $hmac = $payload['hmac'] ?? null;
        $shop = $payload['shop'] ?? null;
        $state = $payload['state'] ?? null;

        foreach (['code' => $code, 'hmac' => $hmac, 'shop' => $shop, 'state' => $state] as $key => $value) {
            if (! is_string($value) || $value === '') {
                throw new InvalidArgumentException(sprintf('Missing Shopify callback query value [%s].', $key));
            }
        }

        $host = $payload['host'] ?? null;
        $timestamp = $payload['timestamp'] ?? null;

        return new self(
            code: $code,
            hmac: $hmac,
            shop: $shop,
            state: $state,
            host: is_string($host) ? $host : null,
            timestamp: is_string($timestamp) ? $timestamp : null,
        );
    }
}
