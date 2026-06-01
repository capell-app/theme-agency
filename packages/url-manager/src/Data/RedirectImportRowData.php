<?php

declare(strict_types=1);

namespace Capell\UrlManager\Data;

use Spatie\LaravelData\Data;

final class RedirectImportRowData extends Data
{
    public function __construct(
        public readonly string $sourceUrl,
        public readonly string $targetUrl,
        public readonly ?int $siteId = null,
        public readonly ?int $languageId = null,
        public readonly int $statusCode = 301,
        public readonly string $matchType = 'exact',
        public readonly string $status = 'active',
        public readonly bool $preserveQuery = true,
        public readonly ?string $notes = null,
    ) {}
}
