<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class PackageOwnershipInspectionData extends Data
{
    /**
     * @param  DataCollection<int, PackageOwnershipCandidateData>  $candidates
     */
    public function __construct(
        public readonly string $kind,
        public readonly string $name,
        public readonly DataCollection $candidates,
        public readonly int $foundCount,
    ) {}
}
