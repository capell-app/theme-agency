<?php

declare(strict_types=1);

namespace Capell\Experiments\Models;

use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property int $id
 * @property int $experiment_id
 * @property AudienceRuleType $type
 * @property string $key
 * @property AudienceOperator $operator
 * @property mixed $value
 * @property bool $is_required
 * @property bool $is_active
 * @property int $sort_order
 */
final class ExperimentAudienceRule extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'experiment_id',
        'type',
        'key',
        'operator',
        'value',
        'is_required',
        'is_active',
        'sort_order',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-experiments.tables.audience_rules');

        return is_string($tableName) ? $tableName : 'experiment_audience_rules';
    }

    /**
     * @return BelongsTo<Experiment, $this>
     */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => AudienceRuleType::class,
            'operator' => AudienceOperator::class,
            'value' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
