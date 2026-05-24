<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property CarbonImmutable $metric_date
 * @property int $screen_page_views
 * @property int $sessions
 * @property int $total_users
 * @property float $average_session_duration
 */
final class GA4ReportsDailyMetric extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-ga4-reports.tables.daily_metrics');

        return is_string($tableName) ? $tableName : 'ga4_reports_daily_metrics';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'metric_date' => 'immutable_date',
            'engagement_rate' => 'float',
            'average_session_duration' => 'float',
        ];
    }
}
