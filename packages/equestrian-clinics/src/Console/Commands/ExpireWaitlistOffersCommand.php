<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Console\Commands;

use Capell\EquestrianClinics\Actions\ExpireWaitlistOffersAction;
use Illuminate\Console\Command;

final class ExpireWaitlistOffersCommand extends Command
{
    protected $signature = 'capell:equestrian-clinics-expire-waitlist-offers {--json : Output the expiry summary as JSON}';

    protected $description = 'Expire stale Equestrian Clinics waitlist offer windows.';

    public function handle(): int
    {
        $expiredCount = ExpireWaitlistOffersAction::run();

        if ($this->option('json') === true) {
            $this->line(json_encode([
                'expired_offers' => $expiredCount,
            ], JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->components->info((string) __('capell-equestrian-clinics::package.commands.expire_waitlist_offers.summary', [
            'count' => $expiredCount,
        ]));

        return self::SUCCESS;
    }
}
