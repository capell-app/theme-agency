<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Data;

use Spatie\LaravelData\Data;

final class InfrastructureStatusData extends Data
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $status,
        public readonly string $detail,
    ) {}
}
