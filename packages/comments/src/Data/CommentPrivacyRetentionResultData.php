<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Spatie\LaravelData\Data;

final class CommentPrivacyRetentionResultData extends Data
{
    public function __construct(
        public readonly int $retentionDays,
        public readonly bool $dryRun,
        public readonly int $matchedAuthors,
        public readonly int $anonymizedAuthors,
        public readonly int $matchedAuthorComments,
        public readonly int $anonymizedAuthorComments,
        public readonly int $matchedAuthorTokens,
        public readonly int $deletedAuthorTokens,
        public readonly int $matchedExpiredTokens,
        public readonly int $deletedExpiredTokens,
        public readonly int $matchedStaleCommentIdentifiers,
        public readonly int $prunedStaleCommentIdentifiers,
    ) {}

    public function matchedRecords(): int
    {
        return $this->matchedAuthors
            + $this->matchedAuthorComments
            + $this->matchedAuthorTokens
            + $this->matchedExpiredTokens
            + $this->matchedStaleCommentIdentifiers;
    }

    public function affectedRecords(): int
    {
        return $this->anonymizedAuthors
            + $this->anonymizedAuthorComments
            + $this->deletedAuthorTokens
            + $this->deletedExpiredTokens
            + $this->prunedStaleCommentIdentifiers;
    }
}
