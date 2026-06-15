<?php

declare(strict_types=1);

namespace Capell\AccessGate\Console\Commands;

use Capell\AccessGate\Actions\PruneAccessGateRecordsAction;
use Capell\AccessGate\Data\PrunedAccessGateRecordsData;
use Illuminate\Console\Command;

final class AccessGatePruneCommand extends Command
{
    protected $signature = 'capell:access-gate-prune
        {--dry-run : Report stale record counts without deleting them}';

    protected $description = 'Prune stale Access Gate tokens, expired registrations, and audit events.';

    public function handle(PruneAccessGateRecordsAction $pruneRecords): int
    {
        $result = $pruneRecords->handle(dryRun: (bool) $this->option('dry-run'));

        if ($this->option('dry-run')) {
            $this->info(__('capell-access-gate::prune.dry_run', ['total' => $result->total()]));
        } else {
            $this->info(__('capell-access-gate::prune.completed', ['total' => $result->total()]));
        }

        $this->outputCounts($result);

        return self::SUCCESS;
    }

    private function outputCounts(PrunedAccessGateRecordsData $result): void
    {
        $this->line(__('capell-access-gate::prune.counts.browser_tokens', ['count' => $result->browserTokens]));
        $this->line(__('capell-access-gate::prune.counts.claim_tokens', ['count' => $result->claimTokens]));
        $this->line(__('capell-access-gate::prune.counts.registrations', ['count' => $result->registrations]));
        $this->line(__('capell-access-gate::prune.counts.events', ['count' => $result->events]));
    }
}
