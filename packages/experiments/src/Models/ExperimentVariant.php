<?php

declare(strict_types=1);

namespace Capell\Experiments\Models;

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
 * @property int $weight
 * @property bool $is_control
 * @property bool $is_active
 * @property int $sort_order
 * @property array<string, mixed>|null $payload
 */
final class ExperimentVariant extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'experiment_id',
        'name',
        'key',
        'weight',
        'is_control',
        'is_active',
        'sort_order',
        'payload',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-experiments.tables.variants');

        return is_string($tableName) ? $tableName : 'experiment_variants';
    }

    /**
     * @return BelongsTo<Experiment, $this>
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * @return HasMany<ExperimentAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(ExperimentAllocation::class, 'experiment_variant_id');
    }

    /**
     * @return HasMany<ExperimentGoalEvent, $this>
     */
    public function goalEvents(): HasMany
    {
        return $this->hasMany(ExperimentGoalEvent::class, 'experiment_variant_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'is_control' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'payload' => 'array',
        ];
    }
}
