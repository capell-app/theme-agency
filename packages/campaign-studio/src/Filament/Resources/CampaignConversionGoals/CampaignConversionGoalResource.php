<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals;

use BackedEnum;
use Capell\Admin\Filament\Concerns\HasConfiguredForm;
use Capell\Admin\Filament\Concerns\HasConfiguredTable;
use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Pages\CreateCampaignConversionGoal;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Pages\EditCampaignConversionGoal;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Pages\ListCampaignConversionGoals;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Schemas\CampaignConversionGoalForm;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Tables\CampaignConversionGoalsTable;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class CampaignConversionGoalResource extends Resource
{
    use HasConfiguredForm;
    use HasConfiguredTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChartBar;

    protected static ?string $recordTitleAttribute = 'name';

    /** @var class-string<CampaignConversionGoalForm> */
    private static string $formConfigurator = CampaignConversionGoalForm::class;

    /** @var class-string<CampaignConversionGoalsTable> */
    private static string $tableConfigurator = CampaignConversionGoalsTable::class;

    /** @return class-string<CampaignConversionGoalForm> */
    public static function getFormConfigurator(): string
    {
        return self::$formConfigurator;
    }

    /** @return class-string<CampaignConversionGoalsTable> */
    public static function getTableConfigurator(): string
    {
        return self::$tableConfigurator;
    }

    #[Override]
    public static function form(Schema $configurator): Schema
    {
        return self::getFormConfigurator()::configure($configurator);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return self::getTableConfigurator()::configure($table);
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('campaignGroup', fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query));
    }

    /** @return class-string<CampaignConversionGoal> */
    #[Override]
    public static function getModel(): string
    {
        return CampaignConversionGoal::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    #[Override]
    public static function getNavigationParentItem(): string
    {
        return __('capell-admin::navigation.marketing_studio');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-campaign-studio::navigation.conversion_goals');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCampaignConversionGoals::route('/'),
            'create' => CreateCampaignConversionGoal::route('/create'),
            'edit' => EditCampaignConversionGoal::route('/{record}/edit'),
        ];
    }
}
