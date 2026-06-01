<?php

declare(strict_types=1);

namespace Capell\UrlManager\Data;

use Capell\UrlManager\Enums\RedirectRuleStatus;
use Spatie\LaravelData\Data;

final class ConvertNotFoundOpportunityData extends Data
{
    public function __construct(
        public readonly int $opportunityId,
        public readonly ?string $targetUrl = null,
        public readonly int $statusCode = 301,
        public readonly RedirectRuleStatus $redirectStatus = RedirectRuleStatus::Active,
        public readonly bool $preserveQuery = true,
        public readonly ?string $notes = null,
        public readonly ?int $createdByUserId = null,
    ) {}
}
