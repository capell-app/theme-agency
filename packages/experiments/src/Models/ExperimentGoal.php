<?php

declare(strict_types=1);

namespace Capell\Experiments\Models;

use Capell\Experiments\Enums\ExperimentGoalType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property int $id
 * @property int $experiment_id
 * @property string $name
 * @property string $key
 * @property ExperimentGoalType $type
 * @property string|null $target
 * @property string|null $value_amount
 * @property bool $is_primary
 * @property bool $is_active
 */
final class ExperimentGoal extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'experiment_id',
        'name',
        'key',
        'type',
        'target',
        'value_amount',
        'is_primary',
        'is_active',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-experiments.tables.goals');

        return is_string($tableName) ? $tableName : 'experiment_goals';
    }

    /**
     * @return BelongsTo<Experiment, $this>
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * @return HasMany<ExperimentGoalEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(ExperimentGoalEvent::class, 'experiment_goal_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => ExperimentGoalType::class,
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'value_amount' => 'decimal:2',
        ];
    }
}
