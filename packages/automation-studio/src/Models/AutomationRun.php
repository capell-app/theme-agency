<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Models;

use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property AutomationTriggerType $trigger_type
 * @property AutomationActionType|null $action_type
 * @property AutomationRunStatus $status
 * @property array<string, mixed>|null $payload
 * @property array<string, mixed>|null $context
 */
class AutomationRun extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'automation_rule_id',
        'site_id',
        'rule_key',
        'action_key',
        'trigger_type',
        'action_type',
        'source_type',
        'source_id',
        'idempotency_key',
        'attempt_number',
        'max_attempts',
        'queued_at',
        'status',
        'message',
        'payload',
        'context',
        'started_at',
        'finished_at',
    ];

    /**
     * @return BelongsTo<AutomationRule, $this>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'trigger_type' => AutomationTriggerType::class,
            'action_type' => AutomationActionType::class,
            'status' => AutomationRunStatus::class,
            'attempt_number' => 'integer',
            'max_attempts' => 'integer',
            'payload' => 'encrypted:array',
            'context' => 'encrypted:array',
            'queued_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }
}
