<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianTourDaySlot run(EquestrianTourDaySlot $slot, EquestrianHorseProfile $horseProfile)
 */
final class AllocateHorseToSlotAction
{
    use AsAction;

    public function handle(EquestrianTourDaySlot $slot, EquestrianHorseProfile $horseProfile): EquestrianTourDaySlot
    {
        return DB::transaction(function () use ($slot, $horseProfile): EquestrianTourDaySlot {
            /** @var EquestrianTourDaySlot $lockedSlot */
            $lockedSlot = EquestrianTourDaySlot::query()
                ->whereKey($slot->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $durationMinutes = (int) max(0, $lockedSlot->starts_at->diffInMinutes($lockedSlot->ends_at));
            $existingWorkloadMinutes = $this->existingWorkloadMinutes($horseProfile, $lockedSlot);

            if ($existingWorkloadMinutes + $durationMinutes > $horseProfile->daily_workload_limit_minutes) {
                throw ValidationException::withMessages([
                    'horse_profile_id' => __('capell-equestrian-clinics::validation.horse_workload_limit_exceeded'),
                ]);
            }

            $lockedSlot->forceFill([
                'meta' => [
                    ...($lockedSlot->meta ?? []),
                    'horse_profile_id' => $horseProfile->id,
                ],
            ])->save();

            return $lockedSlot->refresh();
        });
    }

    private function existingWorkloadMinutes(EquestrianHorseProfile $horseProfile, EquestrianTourDaySlot $slot): int
    {
        $slots = EquestrianTourDaySlot::query()
            ->whereDate('starts_at', $slot->starts_at->toDateString())
            ->where('id', '!=', $slot->getKey())
            ->get();

        return $slots
            ->filter(static fn (EquestrianTourDaySlot $existingSlot): bool => ($existingSlot->meta['horse_profile_id'] ?? null) === $horseProfile->id)
            ->sum(static fn (EquestrianTourDaySlot $existingSlot): int => (int) max(0, $existingSlot->starts_at->diffInMinutes($existingSlot->ends_at)));
    }
}
