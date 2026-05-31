<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Console\Commands;

use Capell\SeoSuite\Actions\RefreshStaleAiDiscoveryMarkdownAction;
use Illuminate\Console\Command;

final class RefreshAiDiscoveryMarkdownCommand extends Command
{
    protected $description = 'Regenerate stale AI Discovery page Markdown snapshots';

    protected $signature = 'capell:seo-suite:refresh-ai-discovery-markdown
                            {--limit= : Maximum number of stale page profiles to refresh}';

    public function handle(): int
    {
        $refreshed = RefreshStaleAiDiscoveryMarkdownAction::run($this->limit());

        $this->info(sprintf('Refreshed %d stale AI Discovery page Markdown profile%s.', $refreshed, $refreshed === 1 ? '' : 's'));

        return Command::SUCCESS;
    }

    private function limit(): ?int
    {
        $limit = $this->option('limit');

        if (! is_numeric($limit)) {
            return null;
        }

        return max(0, (int) $limit);
    }
}
