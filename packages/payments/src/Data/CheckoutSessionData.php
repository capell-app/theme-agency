<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class CheckoutSessionData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $providerPayload
     */
    public function __construct(
        public PaymentProvider $provider,
        public string $providerSessionId,
        public CheckoutSessionStatus $status,
        public CheckoutMode $mode,
        public PaymentPurpose $purpose,
        public ?string $url = null,
        public ?string $currency = null,
        public ?int $amountSubtotal = null,
        public ?int $amountTotal = null,
        public ?string $providerCustomerId = null,
        public ?string $customerEmail = null,
        public ?string $customerName = null,
        public ?string $providerPaymentIntentId = null,
        public ?string $providerSubscriptionId = null,
        public ?CarbonInterface $expiresAt = null,
        public ?CarbonInterface $completedAt = null,
        public array $metadata = [],
        public array $providerPayload = [],
    ) {}
}
