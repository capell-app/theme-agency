<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Data;

use Spatie\LaravelData\Data;

final class AgentDeliveryChunkBudgetData extends Data
{
    /**
     * @param  list<string>  $warnings
     */
    public function __construct(
        public readonly int $chunkCount,
        public readonly int $targetWords,
        public readonly int $maxRecommendedChunks,
        public readonly int $maxChunkWords,
        public readonly int $overTargetChunks,
        public readonly bool $isWithinBudget,
        public readonly array $warnings = [],
    ) {}
}
