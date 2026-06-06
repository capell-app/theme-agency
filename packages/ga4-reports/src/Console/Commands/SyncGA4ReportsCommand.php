<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Console\Commands;

use Capell\GA4Reports\Actions\SyncGA4ReportsMetricsAction;
use Illuminate\Console\Command;

final class SyncGA4ReportsCommand extends Command
{
    protected $signature = 'capell:ga4-reports-sync';

    /**
     * @var list<string>
     */
    protected $aliases = ['ga4-reports:sync'];

    protected $description = 'Sync GA4 Reports metrics into local dashboard snapshots.';

    public function handle(): int
    {
        $result = SyncGA4ReportsMetricsAction::run();

        $this->line($result->message);

        if (! $result->synced) {
            return self::SUCCESS;
        }

        $this->info(__('capell-ga4-reports::sync.summary', [
            'daily' => $result->dailyRows,
            'pages' => $result->pageRows,
        ]));

        return self::SUCCESS;
    }
}
