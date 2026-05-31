<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Capell\Payments\Enums\PaymentIntentStatus;
use Capell\Payments\Enums\PaymentProvider;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class PaymentIntentData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $providerPayload
     */
    public function __construct(
        public PaymentProvider $provider,
        public string $providerPaymentIntentId,
        public PaymentIntentStatus $status,
        public int $amount,
        public string $currency,
        public ?string $providerCustomerId = null,
        public ?string $providerSessionId = null,
        public array $metadata = [],
        public array $providerPayload = [],
    ) {}
}
