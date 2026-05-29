<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Spatie\LaravelData\Data;

final class PageSpeedAuditTargetData extends Data
{
    public function __construct(
        public readonly Page $page,
        public readonly Site $site,
        public readonly Language $language,
        public readonly string $url,
    ) {}
}
