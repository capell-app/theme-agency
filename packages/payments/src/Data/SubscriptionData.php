<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\SubscriptionStatus;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class SubscriptionData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $providerPayload
     */
    public function __construct(
        public PaymentProvider $provider,
        public string $providerSubscriptionId,
        public SubscriptionStatus $status,
        public ?string $providerCustomerId = null,
        public ?string $providerSessionId = null,
        public ?CarbonInterface $trialEndsAt = null,
        public ?CarbonInterface $currentPeriodStartsAt = null,
        public ?CarbonInterface $currentPeriodEndsAt = null,
        public ?CarbonInterface $cancelAt = null,
        public ?CarbonInterface $canceledAt = null,
        public array $metadata = [],
        public array $providerPayload = [],
    ) {}
}
