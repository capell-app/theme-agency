<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class PublicKnowledgeBaseArticleData extends Data
{
    /**
     * @param  list<PublicKnowledgeBaseArticleData>  $relatedArticles
     */
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly string $collectionTitle,
        public readonly string $collectionSlug,
        public readonly string $publicPath,
        public readonly ?string $summary,
        public readonly string $body,
        public readonly string $version,
        public readonly ?CarbonImmutable $lastModified,
        public readonly int $feedbackCount = 0,
        public readonly int $helpfulFeedbackCount = 0,
        public readonly ?int $helpfulFeedbackPercentage = null,
        public readonly array $relatedArticles = [],
    ) {}
}
