<?php

declare(strict_types=1);

namespace Capell\Bookings\Console;

use Capell\Bookings\Actions\InstallBookingsDemoAction;
use Illuminate\Console\Command;

final class InstallBookingsDemoCommand extends Command
{
    protected $signature = 'capell:bookings-demo';

    protected $description = 'Install demo booking services, availability, and appointment requests.';

    public function handle(): int
    {
        $result = InstallBookingsDemoAction::run();

        $this->components->info(sprintf(
            'Installed bookings demo fixtures: %d service, %d staff member, %d location, %d availability window, %d appointment request.',
            $result['services'],
            $result['staff'],
            $result['locations'],
            $result['availability_windows'],
            $result['appointment_requests'],
        ));

        return self::SUCCESS;
    }
}
