<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Capell\Payments\Enums\PaymentDisputeStatus;
use Capell\Payments\Enums\PaymentProvider;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class PaymentDisputeData extends Data
{
    /**
     * @param  array<string, mixed>  $providerPayload
     */
    public function __construct(
        public PaymentProvider $provider,
        public string $providerDisputeId,
        public PaymentDisputeStatus $status,
        public int $amount,
        public string $currency,
        public ?string $providerPaymentIntentId = null,
        public ?string $providerChargeId = null,
        public ?string $reason = null,
        public bool $isChargeRefundable = false,
        public ?CarbonInterface $evidenceDueAt = null,
        public array $providerPayload = [],
    ) {}
}
