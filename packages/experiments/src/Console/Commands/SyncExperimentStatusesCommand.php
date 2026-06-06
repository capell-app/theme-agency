<?php

declare(strict_types=1);

namespace Capell\Experiments\Console\Commands;

use Capell\Experiments\Actions\SyncExperimentStatusesAction;
use Illuminate\Console\Command;

final class SyncExperimentStatusesCommand extends Command
{
    protected $signature = 'capell:experiments:sync-statuses';

    protected $description = 'Transition scheduled and expired experiments based on their date windows.';

    public function handle(): int
    {
        $result = SyncExperimentStatusesAction::run();

        $this->components->info(sprintf(
            'Experiment statuses synced. Activated: %d, ended: %d.',
            $result->scheduledToActive,
            $result->expiredToEnded,
        ));

        return self::SUCCESS;
    }
}
