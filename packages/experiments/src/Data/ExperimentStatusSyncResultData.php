<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Spatie\LaravelData\Data;

final class ExperimentStatusSyncResultData extends Data
{
    public function __construct(
        public int $scheduledToActive,
        public int $expiredToEnded,
    ) {}
}
