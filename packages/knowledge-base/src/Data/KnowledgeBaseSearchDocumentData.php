<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class KnowledgeBaseSearchDocumentData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly string $publicPath,
        public readonly ?string $summary,
        public readonly string $body,
        public readonly int $weight,
        public readonly ?CarbonImmutable $lastModified,
    ) {}
}
