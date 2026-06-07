<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

final class SeoAuthoringQualityGateReadinessRowData extends Data
{
    /**
     * @param  list<string>  $messages
     */
    public function __construct(
        public int $pageId,
        public string $pageName,
        public int $languageId,
        public string $languageName,
        public array $messages,
    ) {}
}
