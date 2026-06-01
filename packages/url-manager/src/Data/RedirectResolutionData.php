<?php

declare(strict_types=1);

namespace Capell\UrlManager\Data;

use Capell\UrlManager\Enums\RedirectMatchType;
use Spatie\LaravelData\Data;

final class RedirectResolutionData extends Data
{
    public function __construct(
        public readonly int $redirectRuleId,
        public readonly string $sourceUrl,
        public readonly string $targetUrl,
        public readonly int $statusCode,
        public readonly RedirectMatchType $matchType,
        public readonly bool $preserveQuery,
    ) {}
}
