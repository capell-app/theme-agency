<?php

declare(strict_types=1);

namespace Capell\Bookings\Console;

use Capell\Bookings\Actions\ScheduleReviewRequestsAction;
use Illuminate\Console\Command;

final class ScheduleBookingReviewRequestsCommand extends Command
{
    protected $signature = 'capell:bookings:schedule-review-requests';

    protected $description = 'Schedule booking review requests for completed appointments.';

    public function handle(): int
    {
        $scheduled = ScheduleReviewRequestsAction::run();

        $this->components->info(sprintf('Scheduled %d booking review request%s.', $scheduled, $scheduled === 1 ? '' : 's'));

        return self::SUCCESS;
    }
}
