<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class CampaignExperimentVariantResultData extends Data
{
    public function __construct(
        public string $variantKey,
        public string $variantName,
        public bool $isControl,
        public int $allocations,
        public int $conversions,
        public float $conversionRate,
        public ?float $liftPercent,
        public bool $isWinner,
    ) {}
}
