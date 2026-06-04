<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class WinnerVariantReportData extends Data
{
    public function __construct(
        public int $variantId,
        public string $variantKey,
        public string $variantName,
        public int $allocations,
        public int $conversions,
        public float $conversionRate,
        public bool $isBaseline = false,
        public bool $meetsSampleSize = false,
        public ?float $lift = null,
        public ?float $pValue = null,
        public bool $isStatisticallySignificant = false,
        public bool $isWinner = false,
    ) {}
}
