<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Models;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class PageSpeedAuditResult extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<PageSpeedAuditRun, $this>
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(PageSpeedAuditRun::class, 'page_speed_audit_run_id');
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function performanceBand(): string
    {
        if ($this->status !== 'succeeded') {
            return 'failed';
        }

        if ($this->performance_score === null) {
            return 'no_data';
        }

        if ($this->performance_score >= 90) {
            return 'good';
        }

        if ($this->performance_score >= 50) {
            return 'needs_improvement';
        }

        return 'poor';
    }

    /**
     * @param  Builder<PageSpeedAuditResult>  $query
     */
    protected function scopeSuccessful(Builder $query): void
    {
        $query->where('status', 'succeeded');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'strategy' => PageSpeedStrategyEnum::class,
            'metrics' => 'array',
            'opportunities' => 'array',
            'diagnostics' => 'array',
            'fetched_at' => 'datetime',
        ];
    }
}
