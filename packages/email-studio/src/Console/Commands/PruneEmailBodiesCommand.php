<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Console\Commands;

use Capell\EmailStudio\Actions\PruneEmailBodiesAction;
use Capell\EmailStudio\Data\EmailBodyPruneResultData;
use Illuminate\Console\Command;

final class PruneEmailBodiesCommand extends Command
{
    protected $signature = 'capell-email-studio:prune-bodies
        {--days= : Override the configured rendered body retention window in days}
        {--dry-run : Report matched messages without clearing rendered bodies}
        {--json : Output the retention summary as JSON}';

    protected $description = 'Clear retained Email Studio rendered bodies after the configured retention window.';

    public function handle(): int
    {
        $days = $this->positiveIntegerOption('days');

        if ($days === 0) {
            return self::FAILURE;
        }

        $result = PruneEmailBodiesAction::run(
            retentionDays: $days,
            dryRun: $this->option('dry-run') === true,
        );

        if ($this->option('json') === true) {
            $this->line(json_encode($this->row($result), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->components->info((string) __('capell-email-studio::package.commands.prune_bodies.summary', [
            'matched' => $result->matchedMessages,
            'pruned' => $result->prunedMessages,
            'days' => $result->retentionDays,
        ]));

        if ($result->dryRun) {
            $this->components->info((string) __('capell-email-studio::package.commands.prune_bodies.dry_run'));
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
     * @return array{retention_days: int, dry_run: bool, matched_messages: int, pruned_messages: int}
     */
    private function row(EmailBodyPruneResultData $result): array
    {
        return [
            'retention_days' => $result->retentionDays,
            'dry_run' => $result->dryRun,
            'matched_messages' => $result->matchedMessages,
            'pruned_messages' => $result->prunedMessages,
        ];
    }
}
