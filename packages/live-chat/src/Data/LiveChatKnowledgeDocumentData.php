<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Capell\LiveChat\Enums\KnowledgeSourceType;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LiveChatKnowledgeDocumentData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly KnowledgeSourceType $sourceType,
        public readonly string $sourceKey,
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $url = null,
        public readonly ?int $sourceId = null,
        public readonly ?int $siteId = null,
        public readonly ?int $installationId = null,
        public readonly int $chunkIndex = 0,
        public readonly array $metadata = [],
    ) {}
}
