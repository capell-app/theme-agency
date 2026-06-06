<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Data\Health;

use Spatie\LaravelData\Data;

final class ExtensionHealthTrendData extends Data
{
    public function __construct(
        public readonly string $currentStatus,
        public readonly int $currentScore,
        public readonly ?string $previousStatus = null,
        public readonly ?int $previousScore = null,
        public readonly ?int $scoreDelta = null,
        public readonly ?string $recordedAt = null,
    ) {}
}
