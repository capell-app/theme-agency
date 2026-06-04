<?php

declare(strict_types=1);

namespace Capell\Comments\Console\Commands;

use Capell\Comments\Actions\ApplyCommentPrivacyRetentionAction;
use Capell\Comments\Data\CommentPrivacyRetentionResultData;
use Illuminate\Console\Command;

final class PruneCommentPrivacyDataCommand extends Command
{
    protected $signature = 'capell-comments:privacy-retention
        {--days= : Override the configured retention window in days}
        {--email= : Anonymize comment author records matching this email hash}
        {--site-id= : Scope email erasure to a single site ID}
        {--dry-run : Report matched records without changing data}
        {--json : Output the retention summary as JSON}';

    protected $description = 'Prune old Comments visitor hashes, moderation notes, tokens, and optional author PII.';

    public function handle(): int
    {
        $days = $this->positiveIntegerOption('days');
        $siteId = $this->positiveIntegerOption('site-id');
        $email = $this->emailOption();

        if ($days === 0 || $siteId === 0 || $email === false) {
            return self::FAILURE;
        }

        $result = ApplyCommentPrivacyRetentionAction::run(
            retentionDays: $days,
            email: $email,
            siteId: $siteId,
            dryRun: $this->option('dry-run') === true,
        );

        if ($this->option('json') === true) {
            $this->line(json_encode($this->row($result), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->components->info((string) __('capell-comments::messages.privacy_retention_summary', [
            'matched' => $result->matchedRecords(),
            'affected' => $result->affectedRecords(),
        ]));

        if ($result->dryRun) {
            $this->components->info((string) __('capell-comments::messages.privacy_retention_dry_run'));
        }

        return self::SUCCESS;
    }

    private function positiveIntegerOption(string $name): ?int
    {
        $option = $this->option($name);

        if ($option === null || $option === '') {
            return null;
        }

        if (! is_string($option) && ! is_int($option)) {
            $this->error((string) __('capell-comments::messages.privacy_retention_positive_integer', ['option' => '--' . $name]));

            return 0;
        }

        $value = (string) $option;

        if (! ctype_digit($value) || (int) $value < 1) {
            $this->error((string) __('capell-comments::messages.privacy_retention_positive_integer', ['option' => '--' . $name]));

            return 0;
        }

        return (int) $value;
    }

    private function emailOption(): string|null|false
    {
        $option = $this->option('email');

        if ($option === null || $option === '') {
            return null;
        }

        if (! is_string($option) || filter_var($option, FILTER_VALIDATE_EMAIL) === false) {
            $this->error((string) __('capell-comments::messages.privacy_retention_valid_email'));

            return false;
        }

        return $option;
    }

    /**
     * @return array<string, int|bool>
     */
    private function row(CommentPrivacyRetentionResultData $result): array
    {
        return [
            'retention_days' => $result->retentionDays,
            'dry_run' => $result->dryRun,
            'matched_records' => $result->matchedRecords(),
            'affected_records' => $result->affectedRecords(),
            'matched_authors' => $result->matchedAuthors,
            'anonymized_authors' => $result->anonymizedAuthors,
            'matched_author_comments' => $result->matchedAuthorComments,
            'anonymized_author_comments' => $result->anonymizedAuthorComments,
            'matched_author_tokens' => $result->matchedAuthorTokens,
            'deleted_author_tokens' => $result->deletedAuthorTokens,
            'matched_expired_tokens' => $result->matchedExpiredTokens,
            'deleted_expired_tokens' => $result->deletedExpiredTokens,
            'matched_stale_comment_identifiers' => $result->matchedStaleCommentIdentifiers,
            'pruned_stale_comment_identifiers' => $result->prunedStaleCommentIdentifiers,
        ];
    }
}
