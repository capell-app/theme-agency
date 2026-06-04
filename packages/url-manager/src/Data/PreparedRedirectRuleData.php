<?php

declare(strict_types=1);

namespace Capell\UrlManager\Data;

use Spatie\LaravelData\Data;

final class PreparedRedirectRuleData extends Data
{
    public function __construct(
        public readonly string $sourceUrl,
        public readonly string $targetUrl,
        public readonly int $priority,
    ) {}
}
