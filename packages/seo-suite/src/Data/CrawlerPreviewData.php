<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

class CrawlerPreviewData extends Data
{
    /**
     * @param  list<string>  $warnings
     */
    public function __construct(
        public string $key,
        public string $path,
        public string $contentType,
        public string $crawlerUserAgent,
        public string $status,
        public string $summary,
        public int $byteSize,
        public string $contentHash,
        public string $preview,
        public array $warnings = [],
    ) {}
}
