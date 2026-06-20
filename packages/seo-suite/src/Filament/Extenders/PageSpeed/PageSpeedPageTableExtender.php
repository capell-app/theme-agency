<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Extenders\PageSpeed;

use Capell\Admin\Contracts\Extenders\PageTableExtender;
use Capell\Core\Models\Page;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Jobs\RunPageSpeedAuditJob;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\HtmlString;
use Throwable;

final class PageSpeedPageTableExtender implements PageTableExtender
{
    /**
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            TextColumn::make('page_speed')
                ->label(__('capell-seo-suite::generic.pagespeed_column'))
                ->html()
                ->alignCenter()
                ->getStateUsing(fn (Page $record): HtmlString => $this->state($record))
                ->toggleable(),
        ];
    }

    /**
     * @return array<int, BulkAction>
     */
    public function getBulkActions(): array
    {
        return [
            $this->runAuditBulkAction(
                name: 'run-mobile-page-speed',
                label: __('capell-seo-suite::generic.pagespeed_run_mobile_audit'),
                strategies: [PageSpeedStrategyEnum::Mobile],
                icon: Heroicon::OutlinedDevicePhoneMobile,
            ),
            $this->runAuditBulkAction(
                name: 'run-desktop-page-speed',
                label: __('capell-seo-suite::generic.pagespeed_run_desktop_audit'),
                strategies: [PageSpeedStrategyEnum::Desktop],
                icon: Heroicon::OutlinedComputerDesktop,
            ),
            $this->runAuditBulkAction(
                name: 'run-page-speed',
                label: __('capell-seo-suite::generic.pagespeed_run_all_audits'),
                strategies: PageSpeedStrategyEnum::cases(),
                icon: Heroicon::OutlinedRocketLaunch,
            ),
        ];
    }

    /**
     * @return array<int, BaseFilter>
     */
    public function getFilters(): array
    {
        return [
            SelectFilter::make('page_speed_status')
                ->label(__('capell-seo-suite::generic.pagespeed_filter'))
                ->options([
                    'good' => __('capell-seo-suite::generic.pagespeed_band_good'),
                    'needs_improvement' => __('capell-seo-suite::generic.pagespeed_band_needs_improvement'),
                    'poor' => __('capell-seo-suite::generic.pagespeed_band_poor'),
                    'failed' => __('capell-seo-suite::generic.pagespeed_band_failed'),
                    'missing' => __('capell-seo-suite::generic.pagespeed_band_no_data'),
                    'stale' => __('capell-seo-suite::generic.pagespeed_band_stale'),
                ])
                ->query(fn (Builder $query, array $data): Builder => $this->applyStatusFilter($query, $data)),
        ];
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function modifyQuery(Builder $query): Builder
    {
        if (! $query->getModel() instanceof Page) {
            return $query;
        }

        return $query->with([
            'latestMobilePageSpeedAuditResult',
            'latestDesktopPageSpeedAuditResult',
        ]);
    }

    private function state(Page $page): HtmlString
    {
        $results = $this->latestResults($page);
        $mobile = $results[PageSpeedStrategyEnum::Mobile->value] ?? null;
        $desktop = $results[PageSpeedStrategyEnum::Desktop->value] ?? null;

        return new HtmlString(sprintf(
            '<span style="display:inline-flex;gap:.35rem;align-items:center;">%s%s</span>',
            $this->badge(PageSpeedStrategyEnum::Mobile, $mobile),
            $this->badge(PageSpeedStrategyEnum::Desktop, $desktop),
        ));
    }

    /**
     * @param  list<PageSpeedStrategyEnum>  $strategies
     */
    private function runAuditBulkAction(string $name, string $label, array $strategies, Heroicon $icon): BulkAction
    {
        return BulkAction::make($name)
            ->label($label)
            ->icon($icon)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading($label)
            ->modalDescription(__('capell-seo-suite::generic.pagespeed_bulk_audit_confirmation'))
            ->action(function (EloquentCollection $records) use ($strategies): void {
                $requestedBy = auth()->user();
                $requestedBy = $requestedBy instanceof Model ? $requestedBy : null;

                $records
                    ->filter(fn (mixed $record): bool => $record instanceof Page)
                    ->each(function (Page $page) use ($strategies, $requestedBy): void {
                        dispatch(new RunPageSpeedAuditJob(
                            pageId: (int) $page->getKey(),
                            strategies: $strategies,
                            requestedBy: $requestedBy,
                        ));
                    });

                Notification::make('pagespeed-bulk-audit-queued')
                    ->title(__('capell-seo-suite::generic.pagespeed_bulk_audit_queued', [
                        'count' => $records->count(),
                    ]))
                    ->success()
                    ->send();
            });
    }

    private function badge(PageSpeedStrategyEnum $strategy, ?PageSpeedAuditResult $result): string
    {
        $score = $result?->performance_score;
        $band = $result instanceof PageSpeedAuditResult ? $result->performanceBand() : 'no_data';
        $label = $score === null
            ? strtoupper(mb_substr($strategy->value, 0, 1)) . ' -'
            : strtoupper(mb_substr($strategy->value, 0, 1)) . ' ' . $score;

        return sprintf(
            '<span title="%s" style="%s">%s</span>',
            e(__('capell-seo-suite::generic.pagespeed_badge_tooltip', [
                'strategy' => $strategy->getLabel(),
                'status' => __('capell-seo-suite::generic.pagespeed_band_' . $band),
            ])),
            $this->badgeStyle($band),
            e($label),
        );
    }

    private function badgeStyle(string $band): string
    {
        $colors = match ($band) {
            'good' => ['#166534', '#dcfce7'],
            'needs_improvement' => ['#92400e', '#fef3c7'],
            'poor', 'failed' => ['#991b1b', '#fee2e2'],
            default => ['#374151', '#f3f4f6'],
        };

        return sprintf(
            'display:inline-flex;min-width:2.25rem;justify-content:center;border-radius:.375rem;padding:.125rem .375rem;font-size:.75rem;font-weight:600;color:%s;background:%s;',
            $colors[0],
            $colors[1],
        );
    }

    /**
     * @return array<string, PageSpeedAuditResult>
     */
    private function latestResults(Page $page): array
    {
        if ($page->relationLoaded('latestMobilePageSpeedAuditResult') || $page->relationLoaded('latestDesktopPageSpeedAuditResult')) {
            return collect([
                $page->relationLoaded('latestMobilePageSpeedAuditResult') ? $page->getRelation('latestMobilePageSpeedAuditResult') : null,
                $page->relationLoaded('latestDesktopPageSpeedAuditResult') ? $page->getRelation('latestDesktopPageSpeedAuditResult') : null,
            ])
                ->filter(fn (mixed $result): bool => $result instanceof PageSpeedAuditResult)
                ->mapWithKeys(fn (PageSpeedAuditResult $result): array => [$result->strategyEnum()->value => $result])
                ->all();
        }

        return collect(PageSpeedStrategyEnum::cases())
            ->mapWithKeys(fn (PageSpeedStrategyEnum $strategy): array => [
                $strategy->value => PageSpeedAuditResult::query()
                    ->where('page_id', $page->getKey())
                    ->where('strategy', $strategy->value)
                    ->latest('fetched_at')
                    ->latest('id')
                    ->first(),
            ])
            ->filter(fn (mixed $result): bool => $result instanceof PageSpeedAuditResult)
            ->all();
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $data
     * @return Builder<Model>
     */
    private function applyStatusFilter(Builder $query, array $data): Builder
    {
        $value = $data['value'] ?? null;

        if ($value === null || $value === '') {
            return $query;
        }

        if ($value === 'missing') {
            return $query->where(function (Builder $query): void {
                $this->whereMissingStrategy($query, PageSpeedStrategyEnum::Mobile)
                    ->orWhere(fn (Builder $query): Builder => $this->whereMissingStrategy($query, PageSpeedStrategyEnum::Desktop));
            });
        }

        if ($value === 'stale') {
            $cutoff = now()->subDays($this->staleAfterDays());

            return $query->where(function (Builder $query) use ($cutoff): void {
                $this->whereMissingStrategy($query, PageSpeedStrategyEnum::Mobile)
                    ->orWhere(fn (Builder $query): Builder => $this->whereMissingStrategy($query, PageSpeedStrategyEnum::Desktop))
                    ->orWhere(fn (Builder $query): Builder => $this->whereLatestStrategyResult(
                        $query,
                        PageSpeedStrategyEnum::Mobile,
                        fn (QueryBuilder $query): QueryBuilder => $query->where('page_speed_latest.fetched_at', '<', $cutoff),
                    ))
                    ->orWhere(fn (Builder $query): Builder => $this->whereLatestStrategyResult(
                        $query,
                        PageSpeedStrategyEnum::Desktop,
                        fn (QueryBuilder $query): QueryBuilder => $query->where('page_speed_latest.fetched_at', '<', $cutoff),
                    ));
            });
        }

        return $query->where(function (Builder $query) use ($value): void {
            $this->whereLatestStrategyResult(
                $query,
                PageSpeedStrategyEnum::Mobile,
                fn (QueryBuilder $query): QueryBuilder => $this->applyResultBand($query, $value),
            )->orWhere(fn (Builder $query): Builder => $this->whereLatestStrategyResult(
                $query,
                PageSpeedStrategyEnum::Desktop,
                fn (QueryBuilder $query): QueryBuilder => $this->applyResultBand($query, $value),
            ));
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function whereMissingStrategy(Builder $query, PageSpeedStrategyEnum $strategy): Builder
    {
        $pageTable = $query->getModel()->getTable();
        $resultTable = (new PageSpeedAuditResult)->getTable();

        return $query->whereNotExists(function (QueryBuilder $query) use ($pageTable, $resultTable, $strategy): void {
            $query
                ->selectRaw('1')
                ->from($resultTable)
                ->whereColumn($resultTable . '.page_id', $pageTable . '.id')
                ->where($resultTable . '.strategy', $strategy->value);
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @param  callable(QueryBuilder): QueryBuilder  $callback
     * @return Builder<Model>
     */
    private function whereLatestStrategyResult(Builder $query, PageSpeedStrategyEnum $strategy, callable $callback): Builder
    {
        $pageTable = $query->getModel()->getTable();
        $resultTable = (new PageSpeedAuditResult)->getTable();

        return $query->whereExists(function (QueryBuilder $query) use ($pageTable, $resultTable, $strategy, $callback): void {
            $query
                ->selectRaw('1')
                ->from($resultTable . ' as page_speed_latest')
                ->whereColumn('page_speed_latest.page_id', $pageTable . '.id')
                ->where('page_speed_latest.strategy', $strategy->value)
                ->whereRaw(
                    'page_speed_latest.id = (select page_speed_latest_match.id from ' . $resultTable . ' as page_speed_latest_match where page_speed_latest_match.page_id = ' . $pageTable . '.id and page_speed_latest_match.strategy = ? order by page_speed_latest_match.fetched_at desc, page_speed_latest_match.id desc limit 1)',
                    [$strategy->value],
                );

            $callback($query);
        });
    }

    private function applyResultBand(QueryBuilder $query, string $value): QueryBuilder
    {
        return match ($value) {
            'good' => $query->where('status', 'succeeded')->where('performance_score', '>=', 90),
            'needs_improvement' => $query->where('status', 'succeeded')->whereBetween('performance_score', [50, 89]),
            'poor' => $query->where('status', 'succeeded')->where('performance_score', '<', 50),
            'failed' => $query->where('status', 'failed'),
            default => $query,
        };
    }

    private function staleAfterDays(): int
    {
        try {
            return max(1, resolve(SeoSuiteSettings::class)->pagespeed_stale_after_days);
        } catch (Throwable) {
            return 14;
        }
    }
}
