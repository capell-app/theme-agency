<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Spatie\LaravelData\Data;

final class PageSpeedAuditDigestFindingData extends Data
{
    public function __construct(
        public readonly string $url,
        public readonly PageSpeedStrategyEnum $strategy,
        public readonly ?int $score,
        public readonly ?int $previousScore = null,
        public readonly ?int $drop = null,
    ) {}

    public function label(): string
    {
        $score = $this->score === null ? '-' : (string) $this->score;

        if ($this->drop !== null && $this->previousScore !== null) {
            return sprintf('%s %s: %s (down %d from %d)', $this->strategy->getLabel(), $this->url, $score, $this->drop, $this->previousScore);
        }

        return sprintf('%s %s: %s', $this->strategy->getLabel(), $this->url, $score);
    }
}
