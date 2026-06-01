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
final class SubscriptionEntitlementData extends Data
{
    public function __construct(
        public bool $entitled,
        public ?PaymentProvider $provider = null,
        public ?string $providerCustomerId = null,
        public ?string $providerSubscriptionId = null,
        public ?SubscriptionStatus $status = null,
        public ?CarbonInterface $trialEndsAt = null,
        public ?CarbonInterface $currentPeriodEndsAt = null,
        public ?CarbonInterface $cancelAt = null,
        public ?CarbonInterface $canceledAt = null,
    ) {}
}
