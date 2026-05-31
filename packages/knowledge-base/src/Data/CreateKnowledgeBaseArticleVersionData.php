<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Data;

use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

final class CreateKnowledgeBaseArticleVersionData extends Data
{
    public function __construct(
        public readonly KnowledgeBaseArticle $article,
        public readonly string $version,
        public readonly string $title,
        public readonly string $body,
        public readonly ?string $summary = null,
        public readonly ?Model $author = null,
    ) {}
}
