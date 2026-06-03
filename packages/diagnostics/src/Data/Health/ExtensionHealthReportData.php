<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Data\Health;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

/**
 * A monorepo-wide rollup of every declared health check across installed
 * packages, classifying each as implemented, stub, or broken and capturing the
 * pass/fail outcome of the implemented ones.
 */
final class ExtensionHealthReportData extends Data
{
    /**
     * @param  DataCollection<int, HealthCheckResultData>  $checks
     */
    public function __construct(
        public readonly int $declaredCount,
        public readonly int $implementedCount,
        public readonly int $stubCount,
        public readonly int $brokenCount,
        public readonly int $executedCount,
        public readonly int $passedCount,
        public readonly int $failedCount,
        public readonly DataCollection $checks,
    ) {}
}
