<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class PageSpeedAuditResultData extends Data
{
    /**
     * @param  array<string, int|null>  $categoryScores
     * @param  array<string, array{display_value?: string|null, numeric_value?: float|null}>  $metrics
     * @param  list<PageSpeedAuditItemData>  $opportunities
     * @param  list<PageSpeedAuditItemData>  $diagnostics
     */
    public function __construct(
        public readonly PageSpeedStrategyEnum $strategy,
        public readonly string $url,
        public readonly bool $successful,
        public readonly array $categoryScores = [],
        public readonly array $metrics = [],
        public readonly array $opportunities = [],
        public readonly array $diagnostics = [],
        public readonly ?string $lighthouseVersion = null,
        public readonly ?CarbonImmutable $fetchedAt = null,
        public readonly ?string $errorMessage = null,
    ) {}

    public function performanceScore(): ?int
    {
        return $this->categoryScores['performance'] ?? null;
    }
}
