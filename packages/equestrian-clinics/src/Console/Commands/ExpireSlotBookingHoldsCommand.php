<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Console\Commands;

use Capell\EquestrianClinics\Actions\ExpireSlotBookingHoldsAction;
use Illuminate\Console\Command;

final class ExpireSlotBookingHoldsCommand extends Command
{
    protected $signature = 'capell:equestrian-clinics-expire-holds {--json : Output the expiry summary as JSON}';

    protected $description = 'Expire stale Equestrian Clinics checkout slot holds.';

    public function handle(): int
    {
        $expiredCount = ExpireSlotBookingHoldsAction::run();

        if ($this->option('json') === true) {
            $this->line(json_encode([
                'expired_holds' => $expiredCount,
            ], JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->components->info((string) __('capell-equestrian-clinics::package.commands.expire_holds.summary', [
            'count' => $expiredCount,
        ]));

        return self::SUCCESS;
    }
}
