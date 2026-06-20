<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Models;

use Capell\SeoSuite\Enums\PageSpeedAuditRunStatusEnum;
use Capell\SeoSuite\Enums\PageSpeedAuditTriggerEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 */
class PageSpeedAuditRun extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return HasMany<PageSpeedAuditResult, $this>
     */
    public function results(): HasMany
    {
        return $this->hasMany(PageSpeedAuditResult::class, 'page_speed_audit_run_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'trigger' => PageSpeedAuditTriggerEnum::class,
            'status' => PageSpeedAuditRunStatusEnum::class,
            'scope' => 'array',
            'notified_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
