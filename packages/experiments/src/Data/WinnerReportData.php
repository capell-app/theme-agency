<?php

declare(strict_types=1);

namespace Capell\Experiments\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class WinnerReportData extends Data
{
    /**
     * @param  list<WinnerVariantReportData>  $variants
     */
    public function __construct(
        public int $experimentId,
        public ?int $goalId,
        public int $totalAllocations,
        public int $totalConversions,
        public ?int $winningVariantId,
        public ?string $winningVariantKey,
        public array $variants,
    ) {}
}
