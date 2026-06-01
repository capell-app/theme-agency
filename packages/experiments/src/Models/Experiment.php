<?php

declare(strict_types=1);

namespace Capell\Experiments\Models;

use Capell\Experiments\Enums\AllocationStrategy;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property string $name
 * @property string $key
 * @property ExperimentStatus $status
 * @property ExperimentSubjectType $subject_type
 * @property string|null $subject_class
 * @property int|null $subject_id
 * @property AllocationStrategy $allocation_strategy
 * @property int $traffic_percentage
 * @property CarbonImmutable|null $starts_at
 * @property CarbonImmutable|null $ends_at
 * @property int|null $winning_variant_id
 * @property CarbonImmutable|null $winner_declared_at
 * @property array<string, mixed>|null $metadata
 */
final class Experiment extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'name',
        'key',
        'status',
        'subject_type',
        'subject_class',
        'subject_id',
        'allocation_strategy',
        'traffic_percentage',
        'starts_at',
        'ends_at',
        'winning_variant_id',
        'winner_declared_at',
        'metadata',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-experiments.tables.experiments');

        return is_string($tableName) ? $tableName : 'experiments';
    }

    /**
     * @return HasMany<ExperimentVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ExperimentVariant::class);
    }

    /**
     * @return HasMany<ExperimentGoal, $this>
     */
    public function goals(): HasMany
    {
        return $this->hasMany(ExperimentGoal::class);
    }

    /**
     * @return HasMany<ExperimentAudienceRule, $this>
     */
    public function audienceRules(): HasMany
    {
        return $this->hasMany(ExperimentAudienceRule::class);
    }

    /**
     * @return HasMany<ExperimentAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(ExperimentAllocation::class);
    }

    /**
     * @return HasMany<ExperimentGoalEvent, $this>
     */
    public function goalEvents(): HasMany
    {
        return $this->hasMany(ExperimentGoalEvent::class);
    }

    /**
     * @return BelongsTo<ExperimentVariant, $this>
     */
    public function winningVariant(): BelongsTo
    {
        return $this->belongsTo(ExperimentVariant::class, 'winning_variant_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => ExperimentStatus::class,
            'subject_type' => ExperimentSubjectType::class,
            'allocation_strategy' => AllocationStrategy::class,
            'traffic_percentage' => 'integer',
            'metadata' => 'array',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'winner_declared_at' => 'immutable_datetime',
        ];
    }
}
