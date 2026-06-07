<?php

declare(strict_types=1);

namespace Capell\PublicActions\Console\Commands;

use Capell\PublicActions\Actions\PrunePublicActionSubmissionsAction;
use Illuminate\Console\Command;

final class PrunePublicActionSubmissionsCommand extends Command
{
    protected $signature = 'capell:public-actions:prune-submissions
        {--days= : Override capell-public-actions.submission_retention_days}
        {--dry-run : Count matching submissions without deleting them}
        {--json : Output the prune summary as JSON}';

    protected $description = 'Prune old Public Actions submissions and their dispatch attempts.';

    public function handle(): int
    {
        $retentionDays = $this->retentionDays();

        if ($retentionDays < 1) {
            $this->error((string) __('capell-public-actions::generic.retention.invalid_days'));

            return Command::FAILURE;
        }

        $result = PrunePublicActionSubmissionsAction::run(
            retentionDays: $retentionDays,
            dryRun: (bool) $this->option('dry-run'),
        );

        if ((bool) $this->option('json')) {
            $this->line(json_encode($result->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

            return Command::SUCCESS;
        }

        $this->info((string) __('capell-public-actions::generic.retention.summary', [
            'matched_submissions' => $result->matchedSubmissions,
            'matched_dispatch_attempts' => $result->matchedDispatchAttempts,
            'deleted_submissions' => $result->deletedSubmissions,
            'days' => $result->retentionDays,
            'cutoff' => $result->cutoff->toDateTimeString(),
        ]));

        if ($result->dryRun) {
            $this->comment((string) __('capell-public-actions::generic.retention.dry_run'));
        }

        return Command::SUCCESS;
    }

    private function retentionDays(): int
    {
        $option = $this->option('days');

        if (is_numeric($option)) {
            return (int) $option;
        }

        $configured = config('capell-public-actions.submission_retention_days', 365);

        return is_numeric($configured) ? (int) $configured : 365;
    }
}
