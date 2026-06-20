<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\SeoSuite\Actions\Dashboard\BuildSeoIntelligenceRowsAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;
use Override;

final class SearchIntelligenceFilamentWidget extends BaseWidget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'seo_opportunities';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['xl' => 1];

    protected static ?int $sort = 45;

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => BuildSeoIntelligenceRowsAction::run(5))
            ->queryStringIdentifier('seo-search-intelligence')
            ->paginated(false)
            ->searchable(false)
            ->heading(__('capell-seo-suite::dashboard.search_intelligence'))
            ->emptyStateHeading(__('capell-seo-suite::dashboard.no_search_intelligence_rows'))
            ->emptyStateDescription(__('capell-seo-suite::dashboard.no_search_intelligence_rows_description'))
            ->columns([
                TextColumn::make('type')
                    ->label(__('capell-seo-suite::dashboard.opportunity')),
                TextColumn::make('query')
                    ->label(__('capell-seo-suite::dashboard.query'))
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('url')
                    ->label(__('capell-seo-suite::dashboard.url'))
                    ->limit(50)
                    ->tooltip(fn (mixed $state): ?string => is_string($state) && $state !== '' ? $state : null)
                    ->wrap(),
                TextColumn::make('impressions')
                    ->label(__('capell-seo-suite::dashboard.impressions'))
                    ->numeric(),
                TextColumn::make('average_position')
                    ->label(__('capell-seo-suite::dashboard.average_position')),
            ]);
    }
}
