<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class CreateBillingPortalSessionData extends Data
{
    public function __construct(
        public string $providerCustomerId,
        public ?string $returnUrl = null,
        public ?string $configuration = null,
        public ?string $locale = null,
    ) {}
}
