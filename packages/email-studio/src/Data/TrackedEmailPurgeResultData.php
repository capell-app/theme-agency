<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Data;

final readonly class TrackedEmailPurgeResultData
{
    public function __construct(
        public int $retentionDays,
        public bool $dryRun,
        public int $matchedEmails,
        public int $deletedClicks,
        public int $deletedEmails,
    ) {}
}
