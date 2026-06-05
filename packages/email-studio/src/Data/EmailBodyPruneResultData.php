<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Data;

use Spatie\LaravelData\Data;

final class EmailBodyPruneResultData extends Data
{
    public function __construct(
        public readonly int $retentionDays,
        public readonly bool $dryRun,
        public readonly int $matchedMessages,
        public readonly int $prunedMessages,
    ) {}
}
