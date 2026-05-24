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
 * @property string $page_path
 * @property string|null $page_title
 * @property int $screen_page_views
 * @property int $sessions
 * @property int $total_users
 * @property int $conversions
 */
final class GA4ReportsPageMetric extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-ga4-reports.tables.page_metrics');

        return is_string($tableName) ? $tableName : 'ga4_reports_page_metrics';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'metric_date' => 'immutable_date',
        ];
    }
}
