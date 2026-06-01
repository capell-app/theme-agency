<?php

declare(strict_types=1);

namespace Capell\UrlManager\Data;

use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Spatie\LaravelData\Data;

final class RedirectRuleData extends Data
{
    public function __construct(
        public readonly string $sourceUrl,
        public readonly string $targetUrl,
        public readonly ?int $siteId = null,
        public readonly ?int $languageId = null,
        public readonly int $statusCode = 301,
        public readonly RedirectMatchType $matchType = RedirectMatchType::Exact,
        public readonly RedirectRuleStatus $status = RedirectRuleStatus::Active,
        public readonly bool $preserveQuery = true,
        public readonly ?string $notes = null,
        public readonly ?int $createdByUserId = null,
    ) {}
}
