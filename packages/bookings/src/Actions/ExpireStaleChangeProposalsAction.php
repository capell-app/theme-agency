<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingChangeProposalStatusEnum;
use Capell\Bookings\Models\BookingChangeProposal;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(?CarbonImmutable $now = null)
 */
class ExpireStaleChangeProposalsAction
{
    use AsAction;

    public function handle(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        return BookingChangeProposal::query()
            ->where('status', BookingChangeProposalStatusEnum::Pending->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update(['status' => BookingChangeProposalStatusEnum::Expired->value]);
    }
}
