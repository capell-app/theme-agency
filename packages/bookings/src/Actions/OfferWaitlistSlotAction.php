<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingWaitlistStatusEnum;
use Capell\Bookings\Models\BookingWaitlistEntry;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingWaitlistEntry run(BookingWaitlistEntry $entry, CarbonImmutable $offerExpiresAt, ?CarbonImmutable $offeredAt = null)
 */
class OfferWaitlistSlotAction
{
    use AsAction;

    public function handle(
        BookingWaitlistEntry $entry,
        CarbonImmutable $offerExpiresAt,
        ?CarbonImmutable $offeredAt = null,
    ): BookingWaitlistEntry {
        $entry->forceFill([
            'status' => BookingWaitlistStatusEnum::Offered,
            'offered_at' => $offeredAt ?? CarbonImmutable::now(),
            'offer_expires_at' => $offerExpiresAt,
        ])->save();

        return $entry->refresh();
    }
}
