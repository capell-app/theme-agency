<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Data\Dashboard;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class PackagesInstalledData extends Data
{
    /**
     * @param  DataCollection<int, PackageInfoData>  $packages
     */
    public function __construct(
        public readonly DataCollection $packages,
        public readonly int $healthCheckDeclaredCount = 0,
        public readonly int $healthCheckImplementedCount = 0,
        public readonly int $healthCheckStubCount = 0,
        public readonly int $healthCheckBrokenCount = 0,
    ) {}
}
