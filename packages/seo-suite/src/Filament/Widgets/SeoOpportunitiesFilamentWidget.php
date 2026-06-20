<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\SeoSuite\Actions\Dashboard\BuildSeoOpportunityRowsAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;
use Override;

final class SeoOpportunitiesFilamentWidget extends BaseWidget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'seo_opportunities';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['xl' => 1];

    protected static ?int $sort = 44;

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => BuildSeoOpportunityRowsAction::run(5))
            ->queryStringIdentifier('seo-opportunities')
            ->paginated(false)
            ->searchable(false)
            ->heading(__('capell-seo-suite::dashboard.seo_opportunities'))
            ->emptyStateHeading(__('capell-seo-suite::dashboard.no_seo_opportunities'))
            ->emptyStateDescription(__('capell-seo-suite::dashboard.no_seo_opportunities_description'))
            ->columns([
                TextColumn::make('page')
                    ->label(__('capell-seo-suite::dashboard.page'))
                    ->limit(50)
                    ->tooltip(fn (mixed $state): ?string => is_string($state) && $state !== '' ? $state : null)
                    ->wrap(),
                TextColumn::make('score')
                    ->label(__('capell-seo-suite::dashboard.score'))
                    ->numeric(),
                TextColumn::make('critical_count')
                    ->label(__('capell-seo-suite::dashboard.critical'))
                    ->numeric(),
                TextColumn::make('warning_count')
                    ->label(__('capell-seo-suite::dashboard.warnings'))
                    ->numeric(),
                TextColumn::make('notices')
                    ->label(__('capell-seo-suite::dashboard.notices'))
                    ->numeric(),
            ]);
    }
}
