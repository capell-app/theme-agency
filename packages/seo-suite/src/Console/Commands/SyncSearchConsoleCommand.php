<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Console\Commands;

use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\SyncSearchConsoleInsightsAction;
use Illuminate\Console\Command;

class SyncSearchConsoleCommand extends Command
{
    protected $signature = 'capell:seo-suite-sync-search-console {--site= : Only sync one site ID} {--limit=100 : Maximum Search Console rows per request}';

    protected $description = 'Sync SEO Suite Google Search Console page and query metrics.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $query = Site::query()->orderBy('id');

        if ($this->option('site') !== null) {
            $query->whereKey($this->option('site'));
        }

        $sites = $query->get(['id']);

        if ($sites->isEmpty()) {
            $this->warn('No sites matched the Search Console sync scope.');

            return self::SUCCESS;
        }

        foreach ($sites as $site) {
            $result = SyncSearchConsoleInsightsAction::run((int) $site->getKey(), $limit);

            if ($result['configured'] !== true) {
                $this->warn('Search Console is not configured.');

                return self::SUCCESS;
            }

            $this->info(sprintf(
                'Synced site %d: %d page rows, %d query rows.',
                (int) $site->getKey(),
                $result['synced'],
                $result['query_synced'],
            ));
        }

        return self::SUCCESS;
    }
}
