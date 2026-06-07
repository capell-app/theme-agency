<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class PruneRenderProfilesResultData extends Data
{
    public function __construct(
        public int $retentionDays,
        public CarbonImmutable $cutoff,
        public int $matchedProfiles,
        public int $deletedProfiles,
        public int $deletedFiles,
        public bool $dryRun,
    ) {}
}
