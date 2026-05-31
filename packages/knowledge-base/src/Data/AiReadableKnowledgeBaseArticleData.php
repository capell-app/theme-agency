<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class AiReadableKnowledgeBaseArticleData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly string $publicPath,
        public readonly ?string $summary,
        public readonly string $content,
        public readonly string $version,
        public readonly ?CarbonImmutable $lastModified,
    ) {}
}
