<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Models;

use Capell\Core\Models\Site;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property CarbonInterface $window_start
 * @property CarbonInterface $window_end
 */
class SearchConsoleQueryMetric extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function scopeLatestWindow(Builder $query): Builder
    {
        return $query->whereNotExists(function (\Illuminate\Database\Query\Builder $newerQuery): void {
            $newerQuery
                ->selectRaw('1')
                ->from('search_console_query_metrics as newer_metrics')
                ->whereColumn('newer_metrics.site_id', 'search_console_query_metrics.site_id')
                ->where(function (\Illuminate\Database\Query\Builder $windowQuery): void {
                    $windowQuery
                        ->whereColumn('newer_metrics.window_end', '>', 'search_console_query_metrics.window_end')
                        ->orWhere(function (\Illuminate\Database\Query\Builder $sameEndQuery): void {
                            $sameEndQuery
                                ->whereColumn('newer_metrics.window_end', 'search_console_query_metrics.window_end')
                                ->whereColumn('newer_metrics.window_start', '>', 'search_console_query_metrics.window_start');
                        });
                });
        });
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'window_start' => 'date',
            'window_end' => 'date',
            'ctr' => 'float',
            'average_position' => 'float',
            'previous_ctr' => 'float',
            'previous_average_position' => 'float',
            'position_delta' => 'float',
            'synced_at' => 'datetime',
        ];
    }
}
