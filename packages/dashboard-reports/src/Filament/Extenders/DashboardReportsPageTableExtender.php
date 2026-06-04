<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Filament\Extenders;

use Capell\Admin\Contracts\Extenders\PageTableExtender;
use Capell\Core\Models\Page;
use Capell\DashboardReports\Actions\Dashboard\BuildDefaultContentHealthAction;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class DashboardReportsPageTableExtender implements PageTableExtender
{
    /**
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [];
    }

    /**
     * @return array<int, BulkAction>
     */
    public function getBulkActions(): array
    {
        return [];
    }

    /**
     * @return array<int, BaseFilter>
     */
    public function getFilters(): array
    {
        return [
            SelectFilter::make(BuildDefaultContentHealthAction::PAGE_TABLE_FILTER_KEY)
                ->label(__('capell-dashboard-reports::dashboard.content_health_filter'))
                ->options([
                    'scheduled_pages' => __('capell-dashboard-reports::dashboard.issue_scheduled_pages'),
                    'expired_pages' => __('capell-dashboard-reports::dashboard.issue_expired_pages'),
                    'pages_without_urls' => __('capell-dashboard-reports::dashboard.issue_pages_without_urls'),
                    'stale_pages' => __('capell-dashboard-reports::dashboard.issue_stale_pages', [
                        'days' => BuildDefaultContentHealthAction::DEFAULT_STALE_DAYS,
                    ]),
                ])
                ->query(fn (Builder $query, array $data): Builder => $this->applyHealthFilter($query, $data)),
        ];
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $data
     * @return Builder<Model>
     */
    private function applyHealthFilter(Builder $query, array $data): Builder
    {
        $value = $data['value'] ?? null;

        if (! $query->getModel() instanceof Page || ! is_string($value) || $value === '') {
            return $query;
        }

        return match ($value) {
            'scheduled_pages' => $query->pending(),
            'expired_pages' => $query->expired(),
            'pages_without_urls' => $query->whereDoesntHave('pageUrls'),
            'stale_pages' => $query
                ->publishedDate()
                ->where($query->getModel()->qualifyColumn('updated_at'), '<', now()->subDays(BuildDefaultContentHealthAction::DEFAULT_STALE_DAYS)),
            default => $query,
        };
    }
}
