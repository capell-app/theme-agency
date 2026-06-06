<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Console\Commands;

use Capell\CampaignStudio\Actions\SyncCampaignStatusesAction;
use Illuminate\Console\Command;

final class SyncCampaignStatusesCommand extends Command
{
    protected $signature = 'capell:campaign-studio-sync-statuses';

    protected $description = 'Transition scheduled and ended Campaign Studio campaigns based on their date windows.';

    public function handle(): int
    {
        $result = SyncCampaignStatusesAction::run();

        $this->components->info(sprintf(
            'Campaign statuses synced. Activated: %d, ended: %d.',
            $result['scheduled_to_active'],
            $result['active_to_ended'],
        ));

        return self::SUCCESS;
    }
}
