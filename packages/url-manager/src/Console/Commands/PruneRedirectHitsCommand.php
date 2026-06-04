<?php

declare(strict_types=1);

namespace Capell\UrlManager\Console\Commands;

use Capell\UrlManager\Actions\PruneRedirectHitsAction;
use Illuminate\Console\Command;
use Override;

final class PruneRedirectHitsCommand extends Command
{
    protected $signature = 'url-manager:prune-hits {--days= : Delete redirect-hit rows older than this many days}';

    protected $description = 'Prune old URL Manager redirect-hit rows.';

    #[Override]
    public function getDescription(): string
    {
        return (string) __('capell-url-manager::action.prune_redirect_hits_description');
    }

    public function handle(): int
    {
        $daysOption = $this->option('days');
        $days = is_numeric($daysOption) ? max(1, (int) $daysOption) : null;
        $deleted = PruneRedirectHitsAction::run($days);

        $this->components->info((string) __('capell-url-manager::action.prune_redirect_hits_result', [
            'count' => $deleted,
        ]));

        return self::SUCCESS;
    }
}
