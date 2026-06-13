<?php

declare(strict_types=1);

namespace Capell\Bookings\Console;

use Capell\Bookings\Actions\PruneBookingRetentionDataAction;
use Illuminate\Console\Command;

final class PruneBookingRetentionDataCommand extends Command
{
    protected $signature = 'capell:bookings:prune-retention-data';

    protected $description = 'Prune booking data governed by retention settings.';

    public function handle(): int
    {
        $result = PruneBookingRetentionDataAction::run();

        $this->components->info(sprintf(
            'Pruned %d message log%s and %d travel observation%s.',
            $result['message_logs_deleted'],
            $result['message_logs_deleted'] === 1 ? '' : 's',
            $result['travel_observations_deleted'],
            $result['travel_observations_deleted'] === 1 ? '' : 's',
        ));

        return self::SUCCESS;
    }
}
