<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Capell\Payments\Enums\PaymentProvider;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class PaymentCustomerData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $providerPayload
     */
    public function __construct(
        public PaymentProvider $provider,
        public ?string $providerCustomerId = null,
        public ?int $siteId = null,
        public ?string $billableType = null,
        public ?string $billableId = null,
        public ?string $email = null,
        public ?string $name = null,
        public array $metadata = [],
        public array $providerPayload = [],
    ) {}
}
