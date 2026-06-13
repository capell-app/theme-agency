<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $tour_day_id
 * @property int $rider_profile_id
 * @property int|null $horse_profile_id
 * @property string|null $discipline
 * @property string $class_name
 * @property string|null $score
 * @property string|null $placing
 * @property CarbonImmutable $occurred_at
 * @property string|null $result_notes
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianTourDay|null $tourDay
 * @property-read EquestrianRiderProfile $riderProfile
 * @property-read EquestrianHorseProfile|null $horseProfile
 */
final class EquestrianCompetitionResult extends Model
{
    protected $table = 'equestrian_competition_results';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianTourDay, $this>
     */
    public function tourDay(): BelongsTo
    {
        return $this->belongsTo(EquestrianTourDay::class, 'tour_day_id');
    }

    /**
     * @return BelongsTo<EquestrianRiderProfile, $this>
     */
    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(EquestrianRiderProfile::class, 'rider_profile_id');
    }

    /**
     * @return BelongsTo<EquestrianHorseProfile, $this>
     */
    public function horseProfile(): BelongsTo
    {
        return $this->belongsTo(EquestrianHorseProfile::class, 'horse_profile_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'meta' => 'json',
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
