<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Data;

use Capell\PrivacyCenter\Enums\RetentionAction;
use Spatie\LaravelData\Data;

final class RetentionRuleData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $dataDomain,
        public int $retentionDays,
        public ?int $siteId = null,
        public ?string $recordType = null,
        public RetentionAction $action = RetentionAction::Delete,
        public ?string $legalBasis = null,
        public bool $isActive = true,
        public array $metadata = [],
    ) {}
}
