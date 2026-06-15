<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static void run(EquestrianTourDaySlot $slot, EquestrianRiderProfile $riderProfile, ?EquestrianHorseProfile $horseProfile = null)
 */
final class ValidateRiderHorseEligibilityAction
{
    use AsAction;

    public function handle(EquestrianTourDaySlot $slot, EquestrianRiderProfile $riderProfile, ?EquestrianHorseProfile $horseProfile = null): void
    {
        if ($slot->skill_tier !== null && ! $riderProfile->hasSkillTier($slot->skill_tier)) {
            throw ValidationException::withMessages([
                'rider_profile_id' => __('capell-equestrian-clinics::validation.rider_skill_tier_mismatch'),
            ]);
        }

        if ($horseProfile instanceof EquestrianHorseProfile && $slot->skill_tier !== null && ! $horseProfile->isSuitableFor($slot->skill_tier)) {
            throw ValidationException::withMessages([
                'horse_profile_id' => __('capell-equestrian-clinics::validation.horse_skill_tier_mismatch'),
            ]);
        }
    }
}
