<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ResolvedExperimentVariantData extends Data
{
    /**
     * @param  array<string, mixed>  $variantPayload
     * @param  array<string, string>  $cacheVaryBy
     */
    public function __construct(
        public int $experimentId,
        public string $experimentKey,
        public int $variantId,
        public string $variantKey,
        public string $allocationKey,
        public bool $isNewAllocation,
        public string $cacheVariationKey,
        public array $cacheVaryBy,
        public array $variantPayload = [],
    ) {}
}
