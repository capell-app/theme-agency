<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Console\Commands;

use Capell\PasswordPolicy\Actions\PrunePasswordHistoryAction;
use Illuminate\Console\Command;

final class PrunePasswordHistoryCommand extends Command
{
    protected $signature = 'capell:password-policy:prune-history
        {--keep= : Number of recent password hashes to keep per user}
        {--user-id= : Limit pruning to one user ID}
        {--dry-run : Report matching history rows without deleting them}';

    protected $description = 'Prune old Password Policy history rows.';

    public function handle(): int
    {
        $keep = $this->positiveIntegerOption('keep');
        $userId = $this->positiveIntegerOption('user-id');

        if ($keep === 0 || $userId === 0) {
            $this->components->error((string) __('capell-password-policy::commands.positive_integer_required'));

            return self::FAILURE;
        }

        $count = PrunePasswordHistoryAction::run(
            keepCount: $keep,
            userId: $userId,
            dryRun: (bool) $this->option('dry-run'),
        );

        $this->components->info((string) __('capell-password-policy::commands.prune_history_complete', [
            'count' => $count,
        ]));

        return self::SUCCESS;
    }

    private function positiveIntegerOption(string $name): ?int
    {
        $option = $this->option($name);

        if ($option === null || $option === '') {
            return null;
        }

        if (! is_string($option) && ! is_int($option)) {
            return 0;
        }

        $value = (string) $option;

        return ctype_digit($value) && (int) $value > 0 ? (int) $value : 0;
    }
}
