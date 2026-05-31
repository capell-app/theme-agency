<?php

declare(strict_types=1);

namespace Capell\UrlManager\Data;

use Capell\UrlManager\Enums\NotFoundOpportunityStatus;
use Spatie\LaravelData\Data;

final class NotFoundOpportunityData extends Data
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        public readonly string $sourceUrl,
        public readonly ?int $siteId = null,
        public readonly ?int $languageId = null,
        public readonly ?string $suggestedTargetUrl = null,
        public readonly NotFoundOpportunityStatus $status = NotFoundOpportunityStatus::Open,
        public readonly ?array $context = null,
    ) {}
}
