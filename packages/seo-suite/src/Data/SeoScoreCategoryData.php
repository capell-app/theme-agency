<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

class SeoScoreCategoryData extends Data
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly int $score,
        public readonly int $weight,
        public readonly int $issueCount,
    ) {}
}
