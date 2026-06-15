<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingWaitlistStatusEnum;
use Capell\Bookings\Models\BookingWaitlistEntry;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(?CarbonImmutable $now = null)
 */
class ExpireWaitlistOffersAction
{
    use AsAction;

    public function handle(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        return BookingWaitlistEntry::query()
            ->where('status', BookingWaitlistStatusEnum::Offered)
            ->whereNotNull('offer_expires_at')
            ->where('offer_expires_at', '<=', $now)
            ->update(['status' => BookingWaitlistStatusEnum::Expired]);
    }
}
