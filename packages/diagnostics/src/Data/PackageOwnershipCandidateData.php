<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Data;

use Spatie\LaravelData\Data;

final class PackageOwnershipCandidateData extends Data
{
    public function __construct(
        public readonly string $composerName,
        public readonly string $slug,
        public readonly ?string $displayName,
        public readonly string $source,
        public readonly string $evidence,
        public readonly string $packagePath,
    ) {}
}
