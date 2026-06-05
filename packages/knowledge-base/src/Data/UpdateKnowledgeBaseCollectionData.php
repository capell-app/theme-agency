<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Data;

use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Spatie\LaravelData\Data;

final class UpdateKnowledgeBaseCollectionData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $slug = null,
        public readonly ?string $key = null,
        public readonly ?string $description = null,
        public readonly ?KnowledgeBaseCollection $parent = null,
        public readonly int $sortOrder = 0,
        public readonly bool $isPublic = true,
    ) {}
}
