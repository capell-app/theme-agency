<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Data;

use Capell\PrivacyCenter\Enums\PolicyType;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class PolicyAcceptanceData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $policyKey,
        public string $policyVersion,
        public PolicyType $policyType = PolicyType::Privacy,
        public ?int $siteId = null,
        public ?int $policyId = null,
        public ?string $context = null,
        public ?CarbonInterface $acceptedAt = null,
        public array $metadata = [],
    ) {}
}
