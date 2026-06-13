<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\Core\Models\Site;
use Capell\LiveChat\Enums\EscalationTriggerType;
use Capell\LiveChat\Enums\LiveChatPriority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property string $name
 * @property EscalationTriggerType $trigger_type
 * @property string|null $trigger_value
 * @property string|null $route_to
 * @property LiveChatPriority $priority
 * @property bool $is_active
 * @property array<string, mixed>|null $metadata
 */
class LiveChatEscalationRule extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'is_active',
        'metadata',
        'name',
        'priority',
        'route_to',
        'site_id',
        'trigger_type',
        'trigger_value',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.escalation_rules');

        return is_string($tableName) ? $tableName : 'live_chat_escalation_rules';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'encrypted:array',
            'priority' => LiveChatPriority::class,
            'trigger_type' => EscalationTriggerType::class,
        ];
    }
}
