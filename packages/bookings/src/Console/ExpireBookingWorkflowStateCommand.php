<?php

declare(strict_types=1);

namespace Capell\Bookings\Console;

use Capell\Bookings\Actions\ExpireStaleChangeProposalsAction;
use Capell\Bookings\Actions\ExpireStaleHoldsAction;
use Capell\Bookings\Actions\ExpireWaitlistOffersAction;
use Illuminate\Console\Command;

final class ExpireBookingWorkflowStateCommand extends Command
{
    protected $signature = 'capell:bookings:expire-workflow-state';

    protected $description = 'Expire stale booking holds and change proposals.';

    public function handle(): int
    {
        $holds = ExpireStaleHoldsAction::run();
        $proposals = ExpireStaleChangeProposalsAction::run();
        $waitlistOffers = ExpireWaitlistOffersAction::run();

        $this->components->info(sprintf(
            'Expired %d booking hold%s, %d change proposal%s, and %d waitlist offer%s.',
            $holds,
            $holds === 1 ? '' : 's',
            $proposals,
            $proposals === 1 ? '' : 's',
            $waitlistOffers,
            $waitlistOffers === 1 ? '' : 's',
        ));

        return self::SUCCESS;
    }
}
