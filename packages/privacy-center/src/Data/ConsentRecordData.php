<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Data;

use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class ConsentRecordData extends Data
{
    /**
     * @param  array<string, mixed>  $evidence
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public CookieCategory $category,
        public ConsentDecision $decision,
        public ?int $siteId = null,
        public ?int $policyId = null,
        public ?string $policyVersion = null,
        public ?string $jurisdiction = null,
        public ?CarbonInterface $decidedAt = null,
        public ?CarbonInterface $expiresAt = null,
        public ?CarbonInterface $revokedAt = null,
        public array $evidence = [],
        public array $metadata = [],
    ) {}
}
