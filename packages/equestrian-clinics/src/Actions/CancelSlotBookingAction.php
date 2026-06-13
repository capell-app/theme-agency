<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static bool run(EquestrianSlotBooking $booking, ?CarbonImmutable $now = null)
 */
final class CancelSlotBookingAction
{
    use AsAction;

    public function handle(EquestrianSlotBooking $booking, ?CarbonImmutable $now = null): bool
    {
        $now ??= CarbonImmutable::now();

        return DB::transaction(function () use ($booking, $now): bool {
            $lockedBooking = EquestrianSlotBooking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedBooking->status, [EquestrianSlotBookingStatusEnum::Held, EquestrianSlotBookingStatusEnum::Confirmed], true)) {
                throw ValidationException::withMessages([
                    'booking_id' => __('capell-equestrian-clinics::validation.booking_not_cancellable'),
                ]);
            }

            $refundAllowed = $lockedBooking->refund_available_until instanceof CarbonImmutable
                && $now->lessThanOrEqualTo($lockedBooking->refund_available_until);
            $wasConfirmed = $lockedBooking->status === EquestrianSlotBookingStatusEnum::Confirmed;

            $lockedBooking->forceFill([
                'status' => EquestrianSlotBookingStatusEnum::Cancelled,
                'cancelled_at' => $now,
                'meta' => [
                    ...($lockedBooking->meta ?? []),
                    'refund_allowed' => $refundAllowed,
                ],
            ])->save();

            if ($wasConfirmed) {
                $slot = $lockedBooking->slot()->lockForUpdate()->firstOrFail();
                $slot->forceFill(['booked_count' => max(0, $slot->booked_count - 1)])->save();
            }

            return $refundAllowed;
        });
    }
}
