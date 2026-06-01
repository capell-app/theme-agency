<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Data;

use Capell\PrivacyCenter\Enums\RetentionAction;
use Spatie\LaravelData\Data;

final class RetentionExecutionResultData extends Data
{
    public function __construct(
        public readonly int $ruleId,
        public readonly string $recordType,
        public readonly RetentionAction $action,
        public readonly int $matchedRecords,
        public readonly int $affectedRecords,
    ) {}
}
