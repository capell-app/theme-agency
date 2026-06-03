<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Models;

use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationRuleData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property AutomationTriggerType $trigger_type
 * @property AutomationRuleStatus $status
 * @property array<string, mixed>|null $conditions
 * @property list<array<string, mixed>> $actions
 * @property array<string, mixed>|null $settings
 */
class AutomationRule extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'key',
        'name',
        'trigger_type',
        'status',
        'conditions',
        'actions',
        'settings',
    ];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany<AutomationRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class);
    }

    public function toRuleData(): AutomationRuleData
    {
        return new AutomationRuleData(
            key: $this->key,
            name: $this->name,
            triggerType: $this->trigger_type,
            actions: collect($this->actions)
                ->map(fn (array $action): AutomationRuleActionData => $this->actionDataFromArray($action))
                ->values()
                ->all(),
            status: $this->status,
            conditions: $this->conditions ?? [],
        );
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'trigger_type' => AutomationTriggerType::class,
            'status' => AutomationRuleStatus::class,
            'conditions' => 'encrypted:array',
            'actions' => 'encrypted:array',
            'settings' => 'encrypted:array',
        ];
    }

    /**
     * @param  array<string, mixed>  $action
     */
    private function actionDataFromArray(array $action): AutomationRuleActionData
    {
        $type = $action['type'] ?? null;
        $settings = $action['settings'] ?? [];

        return new AutomationRuleActionData(
            key: (string) ($action['key'] ?? ''),
            type: $type instanceof AutomationActionType ? $type : AutomationActionType::from((string) $type),
            settings: is_array($settings) ? $settings : [],
        );
    }
}
