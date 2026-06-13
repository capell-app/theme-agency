<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianPaymentStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianSlotBooking run(EquestrianSlotBooking $booking, ?CarbonImmutable $now = null)
 */
final class ConfirmSlotBookingPaymentAction
{
    use AsAction;

    public function handle(EquestrianSlotBooking $booking, ?CarbonImmutable $now = null): EquestrianSlotBooking
    {
        $now ??= CarbonImmutable::now();

        return DB::transaction(function () use ($booking, $now): EquestrianSlotBooking {
            $lockedBooking = EquestrianSlotBooking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== EquestrianSlotBookingStatusEnum::Held) {
                throw ValidationException::withMessages([
                    'booking_id' => __('capell-equestrian-clinics::validation.booking_not_confirmable'),
                ]);
            }

            if ($lockedBooking->hold_expires_at instanceof CarbonImmutable && $lockedBooking->hold_expires_at->lessThanOrEqualTo($now)) {
                throw ValidationException::withMessages([
                    'booking_id' => __('capell-equestrian-clinics::validation.booking_hold_expired'),
                ]);
            }

            $lockedBooking->forceFill([
                'status' => EquestrianSlotBookingStatusEnum::Confirmed,
                'payment_status' => EquestrianPaymentStatusEnum::Paid,
                'confirmed_at' => $now,
                'hold_expires_at' => null,
            ])->save();
            $lockedBooking->slot()->increment('booked_count');

            return $lockedBooking->refresh();
        });
    }
}
