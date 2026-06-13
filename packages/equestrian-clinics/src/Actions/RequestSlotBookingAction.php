<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianPaymentStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Capell\Payments\Enums\PaymentProvider;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianSlotBooking run(EquestrianTourDaySlot $slot, EquestrianRiderProfile $riderProfile, ?EquestrianHorseProfile $horseProfile = null, ?PaymentProvider $provider = null, bool $cashPayment = false, int $quotedTotalPence = 0, ?CarbonImmutable $now = null)
 */
final class RequestSlotBookingAction
{
    use AsAction;

    public function handle(
        EquestrianTourDaySlot $slot,
        EquestrianRiderProfile $riderProfile,
        ?EquestrianHorseProfile $horseProfile = null,
        ?PaymentProvider $provider = null,
        bool $cashPayment = false,
        int $quotedTotalPence = 0,
        ?CarbonImmutable $now = null,
    ): EquestrianSlotBooking {
        $now ??= CarbonImmutable::now();
        $slot->loadMissing('tourDay');

        $this->guardBookingWindow($slot, $now);
        ValidateRiderHorseEligibilityAction::run($slot, $riderProfile, $horseProfile);

        if ($cashPayment && ! $riderProfile->isCashApproved()) {
            throw ValidationException::withMessages([
                'payment_method' => __('capell-equestrian-clinics::validation.cash_payment_requires_approval'),
            ]);
        }

        if (! $cashPayment && ! $provider instanceof PaymentProvider) {
            throw ValidationException::withMessages([
                'payment_provider' => __('capell-equestrian-clinics::validation.payment_provider_required'),
            ]);
        }

        return DB::transaction(function () use ($slot, $riderProfile, $horseProfile, $provider, $cashPayment, $quotedTotalPence, $now): EquestrianSlotBooking {
            $lockedSlot = EquestrianTourDaySlot::query()
                ->whereKey($slot->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->remainingCapacity($lockedSlot, $now) <= 0) {
                throw ValidationException::withMessages([
                    'tour_day_slot_id' => __('capell-equestrian-clinics::validation.slot_capacity_exceeded'),
                ]);
            }

            $booking = EquestrianSlotBooking::query()->create([
                'tour_day_slot_id' => $lockedSlot->getKey(),
                'rider_profile_id' => $riderProfile->getKey(),
                'horse_profile_id' => $horseProfile?->getKey(),
                'status' => $cashPayment ? EquestrianSlotBookingStatusEnum::Confirmed : EquestrianSlotBookingStatusEnum::Held,
                'payment_provider' => $provider,
                'payment_status' => $cashPayment ? EquestrianPaymentStatusEnum::CashApproved : EquestrianPaymentStatusEnum::Pending,
                'cash_payment' => $cashPayment,
                'quoted_total_pence' => $quotedTotalPence,
                'hold_expires_at' => $cashPayment ? null : $now->addMinutes((int) config('capell-equestrian-clinics.checkout_hold_minutes', 10)),
                'refund_available_until' => $lockedSlot->tourDay->starts_at->subHours($lockedSlot->tourDay->cancellation_refund_hours),
                'confirmed_at' => $cashPayment ? $now : null,
            ]);

            if ($cashPayment) {
                $lockedSlot->increment('booked_count');
            }

            return $booking;
        });
    }

    private function guardBookingWindow(EquestrianTourDaySlot $slot, CarbonImmutable $now): void
    {
        if ($now->greaterThanOrEqualTo($slot->tourDay->starts_at->subHours($slot->tourDay->booking_lock_hours))) {
            throw ValidationException::withMessages([
                'tour_day_slot_id' => __('capell-equestrian-clinics::validation.booking_window_closed'),
            ]);
        }
    }

    private function remainingCapacity(EquestrianTourDaySlot $slot, CarbonImmutable $now): int
    {
        $activeHolds = EquestrianSlotBooking::query()
            ->where('tour_day_slot_id', $slot->getKey())
            ->where('status', EquestrianSlotBookingStatusEnum::Held)
            ->where('hold_expires_at', '>', $now)
            ->count();

        return max(0, $slot->capacity_max - $slot->booked_count - $activeHolds);
    }
}
