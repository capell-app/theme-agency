<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class VariantAllocationData extends Data
{
    public function __construct(
        public int $experimentId,
        public int $variantId,
        public string $variantKey,
        public string $allocationKey,
        public bool $isNewAllocation,
    ) {}
}
