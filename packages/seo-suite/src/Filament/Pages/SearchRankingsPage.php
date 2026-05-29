<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Capell\Admin\Filament\Concerns\HasNavigationBadge;
use Capell\Admin\Support\SiteScope;
use Capell\SeoSuite\Models\SearchConsoleQueryMetric;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Override;

class SearchRankingsPage extends Page implements HasTable
{
    use HasNavigationBadge;
    use HasPageShield;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChartBar;

    protected static ?int $navigationSort = 15;

    protected string $view = 'capell-admin::components.pages.table';

    protected static ?string $slug = 'search-rankings';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-seo-suite::generic.search_rankings');
    }

    #[Override]
    public static function getNavigationParentItem(): ?string
    {
        return (string) __('capell-seo-suite::generic.seo_audit');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return (string) __('capell-admin::navigation.group_monitoring');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SiteScope::applyForCurrentActor(SearchConsoleQueryMetric::query(), denyWhenMissingActor: true)
                ->with('site')
                ->latestWindow())
            ->columns([
                TextColumn::make('site.name')
                    ->label(__('capell-seo-suite::dashboard.site'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('query')
                    ->label(__('capell-seo-suite::dashboard.query'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('url')
                    ->label(__('capell-seo-suite::dashboard.url'))
                    ->searchable()
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('clicks')
                    ->label(__('capell-seo-suite::dashboard.clicks'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('impressions')
                    ->label(__('capell-seo-suite::dashboard.impressions'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ctr')
                    ->label(__('capell-seo-suite::dashboard.ctr'))
                    ->formatStateUsing(fn (mixed $state): string => number_format((float) $state * 100, 1) . '%')
                    ->sortable(),
                TextColumn::make('average_position')
                    ->label(__('capell-seo-suite::dashboard.average_position'))
                    ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 1))
                    ->sortable(),
                TextColumn::make('click_delta')
                    ->label(__('capell-seo-suite::dashboard.click_delta'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('opportunity')
                    ->label(__('capell-seo-suite::dashboard.opportunity'))
                    ->options([
                        'quick_win' => __('capell-seo-suite::generic.seo_opportunity_quick_win'),
                        'ctr' => __('capell-seo-suite::generic.seo_opportunity_ctr'),
                        'declining' => __('capell-seo-suite::generic.seo_opportunity_declining'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'quick_win' => $query->whereBetween('average_position', [4, 20])->where('impressions', '>=', 100),
                        'ctr' => $query->where('impressions', '>=', 500)->where('ctr', '<', 0.02),
                        'declining' => $query->where('click_delta', '<', 0),
                        default => $query,
                    }),
            ])
            ->defaultSort('impressions', 'desc');
    }

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return __('capell-seo-suite::generic.search_rankings_info');
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        return __('capell-seo-suite::generic.search_rankings');
    }
}
