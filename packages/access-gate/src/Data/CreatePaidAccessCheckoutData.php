<?php

declare(strict_types=1);

namespace Capell\AccessGate\Data;

use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\PaymentProvider;

final class CreatePaidAccessCheckoutData
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $successUrl,
        public readonly string $cancelUrl,
        public readonly string $lineItemName,
        public readonly int $amount,
        public readonly string $currency,
        public readonly int $quantity = 1,
        public readonly PaymentProvider $provider = PaymentProvider::Stripe,
        public readonly CheckoutMode $mode = CheckoutMode::Payment,
        public readonly ?string $lineItemDescription = null,
        public readonly ?string $providerPriceId = null,
        public readonly ?string $customerName = null,
        public readonly ?string $referenceId = null,
        public readonly ?string $idempotencyKey = null,
        public readonly array $metadata = [],
    ) {}
}
