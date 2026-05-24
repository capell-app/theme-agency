<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $status
 * @property CarbonImmutable|null $finished_at
 * @property string|null $error_message
 */
final class GA4ReportsSyncRun extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-ga4-reports.tables.sync_runs');

        return is_string($tableName) ? $tableName : 'ga4_reports_sync_runs';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'window_start' => 'immutable_date',
            'window_end' => 'immutable_date',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }
}
