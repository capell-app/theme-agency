<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianWaitlistStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\EquestrianClinics\Models\EquestrianSlotWaitlistEntry;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianSlotWaitlistEntry run(EquestrianTourDaySlot $slot, EquestrianRiderProfile $riderProfile, ?EquestrianHorseProfile $horseProfile = null, int $quotedTotalPence = 0, ?CarbonImmutable $now = null)
 */
final class JoinSlotWaitlistAction
{
    use AsAction;

    public function handle(
        EquestrianTourDaySlot $slot,
        EquestrianRiderProfile $riderProfile,
        ?EquestrianHorseProfile $horseProfile = null,
        int $quotedTotalPence = 0,
        ?CarbonImmutable $now = null,
    ): EquestrianSlotWaitlistEntry {
        $now ??= CarbonImmutable::now();
        ValidateRiderHorseEligibilityAction::run($slot, $riderProfile, $horseProfile);

        return DB::transaction(function () use ($slot, $riderProfile, $horseProfile, $quotedTotalPence, $now): EquestrianSlotWaitlistEntry {
            $lockedSlot = EquestrianTourDaySlot::query()
                ->whereKey($slot->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->slotIsFull($lockedSlot, $now)) {
                throw ValidationException::withMessages([
                    'tour_day_slot_id' => __('capell-equestrian-clinics::validation.waitlist_requires_full_slot'),
                ]);
            }

            $entry = EquestrianSlotWaitlistEntry::query()->create([
                'tour_day_slot_id' => $lockedSlot->getKey(),
                'rider_profile_id' => $riderProfile->getKey(),
                'horse_profile_id' => $horseProfile?->getKey(),
                'status' => EquestrianWaitlistStatusEnum::Waiting,
                'quoted_total_pence' => $quotedTotalPence,
            ]);

            $lockedSlot->increment('waitlist_count');

            return $entry;
        });
    }

    private function slotIsFull(EquestrianTourDaySlot $slot, CarbonImmutable $now): bool
    {
        $activeHolds = EquestrianSlotBooking::query()
            ->where('tour_day_slot_id', $slot->getKey())
            ->where('status', EquestrianSlotBookingStatusEnum::Held)
            ->where('hold_expires_at', '>', $now)
            ->count();

        return $slot->booked_count + $activeHolds >= $slot->capacity_max;
    }
}
