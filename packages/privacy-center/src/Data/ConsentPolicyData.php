<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Data;

use Capell\PrivacyCenter\Enums\PolicyType;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class ConsentPolicyData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $key,
        public string $version,
        public string $title,
        public PolicyType $type = PolicyType::Privacy,
        public ?int $siteId = null,
        public ?string $contentHash = null,
        public ?CarbonInterface $effectiveAt = null,
        public ?CarbonInterface $publishedAt = null,
        public ?CarbonInterface $retiredAt = null,
        public array $metadata = [],
    ) {}
}
