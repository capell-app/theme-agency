<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianPaymentStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(?CarbonImmutable $now = null)
 */
final class ExpireSlotBookingHoldsAction
{
    use AsAction;

    public function handle(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        return EquestrianSlotBooking::query()
            ->where('status', EquestrianSlotBookingStatusEnum::Held)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', $now)
            ->update([
                'status' => EquestrianSlotBookingStatusEnum::Expired,
                'payment_status' => EquestrianPaymentStatusEnum::Failed,
                'updated_at' => $now,
            ]);
    }
}
