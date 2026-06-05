<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Console\Commands;

use Capell\EmailStudio\Actions\PurgeTrackedEmailsAction;
use Capell\EmailStudio\Data\TrackedEmailPurgeResultData;
use Illuminate\Console\Command;

final class PurgeTrackedEmailsCommand extends Command
{
    protected $signature = 'capell-email-studio:purge-tracked-emails
        {--days= : Override the configured tracked email retention window in days}
        {--dry-run : Report matched emails without deleting tracked rows}
        {--json : Output the retention summary as JSON}';

    protected $description = 'Delete old MailTracker sent email records after the configured retention window.';

    public function handle(): int
    {
        $days = $this->positiveIntegerOption('days');

        if ($days === 0) {
            return self::FAILURE;
        }

        $result = PurgeTrackedEmailsAction::run(
            retentionDays: $days,
            dryRun: $this->option('dry-run') === true,
        );

        if ($this->option('json') === true) {
            $this->line(json_encode($this->row($result), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->components->info((string) __('capell-email-studio::package.commands.purge_tracked_emails.summary', [
            'matched' => $result->matchedEmails,
            'clicks' => $result->deletedClicks,
            'emails' => $result->deletedEmails,
            'days' => $result->retentionDays,
        ]));

        if ($result->dryRun) {
            $this->components->info((string) __('capell-email-studio::package.commands.purge_tracked_emails.dry_run'));
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
            $this->error((string) __('capell-email-studio::package.commands.positive_integer', ['option' => '--' . $name]));

            return 0;
        }

        $value = (string) $option;

        if (! ctype_digit($value) || (int) $value < 1) {
            $this->error((string) __('capell-email-studio::package.commands.positive_integer', ['option' => '--' . $name]));

            return 0;
        }

        return (int) $value;
    }

    /**
     * @return array{retention_days: int, dry_run: bool, matched_emails: int, deleted_clicks: int, deleted_emails: int}
     */
    private function row(TrackedEmailPurgeResultData $result): array
    {
        return [
            'retention_days' => $result->retentionDays,
            'dry_run' => $result->dryRun,
            'matched_emails' => $result->matchedEmails,
            'deleted_clicks' => $result->deletedClicks,
            'deleted_emails' => $result->deletedEmails,
        ];
    }
}
