<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Capell\Payments\Enums\PaymentProvider;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class BillingPortalSessionData extends Data
{
    /**
     * @param  array<string, mixed>  $providerPayload
     */
    public function __construct(
        public PaymentProvider $provider,
        public string $providerSessionId,
        public string $providerCustomerId,
        public string $url,
        public ?string $returnUrl = null,
        public ?string $configuration = null,
        public ?string $locale = null,
        public ?CarbonInterface $createdAt = null,
        public array $providerPayload = [],
    ) {}
}
