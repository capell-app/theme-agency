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
 * @property string $allocation_key
 * @property string $allocation_hash
 * @property string|null $source
 * @property string|null $external_id
 * @property CarbonImmutable $allocated_at
 * @property array<string, mixed>|null $context
 * @property-read ExperimentVariant $variant
 */
final class ExperimentAllocation extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'experiment_id',
        'experiment_variant_id',
        'allocation_key',
        'allocation_hash',
        'source',
        'external_id',
        'context',
        'allocated_at',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-experiments.tables.allocations');

        return is_string($tableName) ? $tableName : 'experiment_allocations';
    }

    /**
     * @return BelongsTo<Experiment, $this>
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
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
            'context' => 'array',
            'allocated_at' => 'immutable_datetime',
        ];
    }
}
