<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianWaitlistStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianSlotWaitlistEntry;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianSlotWaitlistEntry run(EquestrianTourDaySlot $slot, ?CarbonImmutable $now = null)
 */
final class PromoteWaitlistEntryAction
{
    use AsAction;

    public function handle(EquestrianTourDaySlot $slot, ?CarbonImmutable $now = null): EquestrianSlotWaitlistEntry
    {
        $now ??= CarbonImmutable::now();

        return DB::transaction(function () use ($slot, $now): EquestrianSlotWaitlistEntry {
            $entry = EquestrianSlotWaitlistEntry::query()
                ->where('tour_day_slot_id', $slot->getKey())
                ->where('status', EquestrianWaitlistStatusEnum::Waiting)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (! $entry instanceof EquestrianSlotWaitlistEntry) {
                throw ValidationException::withMessages([
                    'waitlist_entry_id' => __('capell-equestrian-clinics::validation.waitlist_empty'),
                ]);
            }

            $claimMinutes = config('capell-equestrian-clinics.waitlist_claim_minutes', 120);
            $claimMinutes = is_numeric($claimMinutes) ? (int) $claimMinutes : 120;

            $entry->forceFill([
                'status' => EquestrianWaitlistStatusEnum::Offered,
                'offered_at' => $now,
                'offer_expires_at' => $now->addMinutes($claimMinutes),
            ])->save();

            return $entry->refresh();
        });
    }
}
