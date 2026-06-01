<?php

declare(strict_types=1);

namespace Capell\Experiments\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $experiment_id
 * @property int $experiment_variant_id
 * @property int $experiment_goal_id
 * @property int|null $experiment_allocation_id
 * @property string|null $event_key
 * @property string|null $value_amount
 * @property CarbonImmutable $occurred_at
 * @property array<string, mixed>|null $metadata
 */
final class ExperimentGoalEvent extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'experiment_id',
        'experiment_variant_id',
        'experiment_goal_id',
        'experiment_allocation_id',
        'event_key',
        'value_amount',
        'metadata',
        'occurred_at',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-experiments.tables.goal_events');

        return is_string($tableName) ? $tableName : 'experiment_goal_events';
    }

    /**
     * @return BelongsTo<Experiment, $this>
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * @return BelongsTo<ExperimentGoal, $this>
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(ExperimentGoal::class, 'experiment_goal_id');
    }

    /**
     * @return BelongsTo<ExperimentAllocation, $this>
     */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(ExperimentAllocation::class, 'experiment_allocation_id');
    }

    /**
     * @return BelongsTo<ExperimentVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ExperimentVariant::class, 'experiment_variant_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'value_amount' => 'decimal:2',
            'metadata' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
