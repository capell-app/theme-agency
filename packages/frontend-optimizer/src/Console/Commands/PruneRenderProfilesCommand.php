<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Console\Commands;

use Capell\FrontendOptimizer\Actions\PruneRenderProfilesAction;
use Illuminate\Console\Command;

final class PruneRenderProfilesCommand extends Command
{
    protected $signature = 'capell:frontend-optimizer:prune-profiles
        {--days=30 : Delete render profiles not updated in this many days}
        {--limit= : Maximum profiles to prune in one run}
        {--dry-run : Report matching profiles without deleting rows or files}
        {--json : Output the prune summary as JSON}';

    protected $description = 'Prune stale Frontend Optimizer render profiles and generated asset files.';

    public function handle(): int
    {
        $result = PruneRenderProfilesAction::run(
            retentionDays: $this->positiveIntegerOption('days') ?? 30,
            limit: $this->positiveIntegerOption('limit'),
            dryRun: (bool) $this->option('dry-run'),
        );

        if ((bool) $this->option('json')) {
            $this->line(json_encode($result->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->components->info((string) __('capell-frontend-optimizer::commands.prune_profiles.summary', [
            'matched' => $result->matchedProfiles,
            'deleted' => $result->deletedProfiles,
            'files' => $result->deletedFiles,
            'days' => $result->retentionDays,
            'cutoff' => $result->cutoff->toDateTimeString(),
        ]));

        if ($result->dryRun) {
            $this->components->warn((string) __('capell-frontend-optimizer::commands.prune_profiles.dry_run'));
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
            return null;
        }

        $value = (string) $option;

        return ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }
}
