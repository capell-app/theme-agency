<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class CreateCheckoutSessionData extends Data
{
    /**
     * @param  list<CheckoutLineItemData>  $lineItems
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $successUrl,
        public string $cancelUrl,
        public array $lineItems,
        public PaymentPurpose $purpose = PaymentPurpose::OneOff,
        public CheckoutMode $mode = CheckoutMode::Payment,
        public PaymentProvider $provider = PaymentProvider::Stripe,
        public ?int $siteId = null,
        public ?string $providerCustomerId = null,
        public ?string $customerEmail = null,
        public ?string $customerName = null,
        public ?string $billableType = null,
        public ?string $billableId = null,
        public ?string $payableType = null,
        public ?string $payableId = null,
        public ?string $sourceType = null,
        public ?string $sourceId = null,
        public ?string $referenceId = null,
        public ?string $idempotencyKey = null,
        public array $metadata = [],
    ) {}
}
