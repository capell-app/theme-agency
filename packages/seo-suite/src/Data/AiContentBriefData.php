<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

class AiContentBriefData extends Data
{
    /**
     * @param  array<array-key, mixed>  $faqIdeas
     * @param  array<array-key, mixed>  $internalLinks
     * @param  array<array-key, mixed>  $metaDescriptionAlternatives
     * @param  array<array-key, mixed>  $metaTitleAlternatives
     * @param  array<array-key, mixed>  $missingTopics
     * @param  array<array-key, mixed>  $schemaOpportunities
     * @param  array<array-key, mixed>  $suggestedHeadings
     */
    public function __construct(
        public string $contentAngle,
        public array $missingTopics,
        public array $suggestedHeadings,
        public array $faqIdeas,
        public array $schemaOpportunities,
        public array $internalLinks,
        public array $metaTitleAlternatives,
        public array $metaDescriptionAlternatives,
    ) {}
}
