<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

class PageContentAnalysisData extends Data
{
    /**
     * @param  list<string>  $headingTags
     */
    public function __construct(
        public readonly ?string $focusKeyword,
        public readonly int $h1Count,
        public readonly bool $headingOrderValid,
        public readonly int $wordCount,
        public readonly int $focusKeywordOccurrences,
        public readonly float $focusKeywordDensity,
        public readonly bool $titleContainsFocusKeyword,
        public readonly bool $urlContainsFocusKeyword,
        public readonly bool $firstParagraphContainsFocusKeyword,
        public readonly array $headingTags = [],
    ) {}

    public function hasFocusKeyword(): bool
    {
        return $this->focusKeyword !== null && $this->focusKeyword !== '';
    }

    public function hasSingleH1(): bool
    {
        return $this->h1Count === 1;
    }
}
