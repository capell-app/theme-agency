<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianCompetitionResult;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianCompetitionResult run(EquestrianRiderProfile $riderProfile, string $className, CarbonImmutable $occurredAt, ?EquestrianTourDay $tourDay = null, ?EquestrianHorseProfile $horseProfile = null, ?string $discipline = null, ?string $score = null, ?string $placing = null, ?string $resultNotes = null, ?array $meta = null)
 */
final class RecordCompetitionResultAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        EquestrianRiderProfile $riderProfile,
        string $className,
        CarbonImmutable $occurredAt,
        ?EquestrianTourDay $tourDay = null,
        ?EquestrianHorseProfile $horseProfile = null,
        ?string $discipline = null,
        ?string $score = null,
        ?string $placing = null,
        ?string $resultNotes = null,
        ?array $meta = null,
    ): EquestrianCompetitionResult {
        return EquestrianCompetitionResult::query()->create([
            'tour_day_id' => $tourDay?->getKey(),
            'rider_profile_id' => $riderProfile->getKey(),
            'horse_profile_id' => $horseProfile?->getKey(),
            'discipline' => $discipline,
            'class_name' => $className,
            'score' => $score,
            'placing' => $placing,
            'occurred_at' => $occurredAt,
            'result_notes' => $resultNotes,
            'meta' => $meta,
        ]);
    }
}
