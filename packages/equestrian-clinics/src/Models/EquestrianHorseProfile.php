<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property int|null $portal_account_id
 * @property string $name
 * @property int|null $age_years
 * @property string|null $fitness_status
 * @property CarbonImmutable|null $vaccinated_until
 * @property int $daily_workload_limit_minutes
 * @property array<int, string>|null $suitable_skill_tiers
 * @property string|null $notes
 * @property bool $active
 * @property-read Collection<int, EquestrianHorseCareTask> $careTasks
 * @property-read Collection<int, EquestrianHorseHealthRecord> $healthRecords
 * @property-read Collection<int, EquestrianCompetitionResult> $competitionResults
 */
final class EquestrianHorseProfile extends Model
{
    protected $table = 'equestrian_horse_profiles';

    protected $guarded = [];

    /**
     * @return HasMany<EquestrianHorseCareTask, $this>
     */
    public function careTasks(): HasMany
    {
        return $this->hasMany(EquestrianHorseCareTask::class, 'horse_profile_id');
    }

    /**
     * @return HasMany<EquestrianHorseHealthRecord, $this>
     */
    public function healthRecords(): HasMany
    {
        return $this->hasMany(EquestrianHorseHealthRecord::class, 'horse_profile_id');
    }

    /**
     * @return HasMany<EquestrianCompetitionResult, $this>
     */
    public function competitionResults(): HasMany
    {
        return $this->hasMany(EquestrianCompetitionResult::class, 'horse_profile_id');
    }

    public function isSuitableFor(string $skillTier): bool
    {
        $suitableSkillTiers = $this->suitable_skill_tiers;

        if (! is_array($suitableSkillTiers) || $suitableSkillTiers === []) {
            return true;
        }

        return in_array($skillTier, $suitableSkillTiers, true);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'age_years' => 'integer',
            'daily_workload_limit_minutes' => 'integer',
            'suitable_skill_tiers' => 'json',
            'vaccinated_until' => 'immutable_date',
        ];
    }
}
