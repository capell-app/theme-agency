<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class CampaignExperimentResultsData extends Data
{
    /**
     * @param  list<CampaignExperimentVariantResultData>  $variants
     */
    public function __construct(
        public int $experimentId,
        public ?int $goalId,
        public int $totalAllocations,
        public int $totalConversions,
        public ?string $winningVariantKey,
        public array $variants,
    ) {}
}
