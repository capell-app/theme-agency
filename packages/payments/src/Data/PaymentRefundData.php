<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentRefundStatus;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class PaymentRefundData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $providerPayload
     */
    public function __construct(
        public PaymentProvider $provider,
        public string $providerRefundId,
        public PaymentRefundStatus $status,
        public int $amount,
        public string $currency,
        public ?string $providerPaymentIntentId = null,
        public ?string $providerChargeId = null,
        public ?string $reason = null,
        public array $metadata = [],
        public array $providerPayload = [],
    ) {}
}
