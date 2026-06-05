<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Data;

use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

final class UpdateKnowledgeBaseArticleData extends Data
{
    public function __construct(
        public readonly KnowledgeBaseCollection $collection,
        public readonly string $title,
        public readonly string $body,
        public readonly ?string $slug = null,
        public readonly ?string $summary = null,
        public readonly ?string $version = null,
        public readonly KnowledgeBaseArticleStatus $status = KnowledgeBaseArticleStatus::Draft,
        public readonly int $searchWeight = 50,
        public readonly bool $isAiReadable = true,
        public readonly ?Model $author = null,
    ) {}
}
