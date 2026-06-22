<?php

declare(strict_types=1);

namespace Capell\AiCreator\Data;

use Capell\AiCreator\Enums\AiCreatorRecommendationLevel;
use Spatie\LaravelData\Data;

final class AiCreatorPackageRecommendationData extends Data
{
    public function __construct(
        public readonly string $package,
        public readonly AiCreatorRecommendationLevel $level,
        public readonly string $reason,
        public readonly string $consequence,
    ) {}

    /**
     * @return array{package: string, level: string, reason: string, consequence: string}
     */
    public function toPayload(): array
    {
        return [
            'package' => $this->package,
            'level' => $this->level->value,
            'reason' => $this->reason,
            'consequence' => $this->consequence,
        ];
    }
}
