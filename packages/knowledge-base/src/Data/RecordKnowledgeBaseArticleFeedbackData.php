<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Data;

use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Spatie\LaravelData\Data;

final class RecordKnowledgeBaseArticleFeedbackData extends Data
{
    public function __construct(
        public readonly KnowledgeBaseArticle $article,
        public readonly bool $helpful,
        public readonly ?KnowledgeBaseArticleVersion $articleVersion = null,
        public readonly ?string $comment = null,
        public readonly ?string $visitorIdentifier = null,
        public readonly ?string $userAgent = null,
    ) {}
}
