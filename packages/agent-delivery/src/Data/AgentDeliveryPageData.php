<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Data;

use Spatie\LaravelData\Data;

final class AgentDeliveryPageData extends Data
{
    /**
     * @param  array<string, string>  $alternates
     * @param  list<string>  $headings
     * @param  array<string, mixed>  $metadata
     * @param  list<array<string, string>>  $references
     * @param  list<string>  $relatedUrls
     */
    public function __construct(
        public readonly string $canonicalUrl,
        public readonly string $url,
        public readonly string $language,
        public readonly array $alternates,
        public readonly ?string $title,
        public readonly array $headings,
        public readonly ?string $summary,
        public readonly ?string $body,
        public readonly array $metadata,
        public readonly array $references,
        public readonly ?string $publishedAt,
        public readonly ?string $lastUpdatedAt,
        public readonly array $relatedUrls,
    ) {}
}
