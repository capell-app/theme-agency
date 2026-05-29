<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Capell\SeoSuite\Enums\SeoOpportunityTypeEnum;
use Spatie\LaravelData\Data;

class SeoOpportunityRowData extends Data
{
    /**
     * @param  list<string>  $urls
     */
    public function __construct(
        public SeoOpportunityTypeEnum $type,
        public string $query,
        public string $url,
        public string $message,
        public int $priority,
        public int $impressions = 0,
        public int $clicks = 0,
        public float $ctr = 0.0,
        public ?float $averagePosition = null,
        public array $urls = [],
    ) {}
}
