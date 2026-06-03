<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets;

use BackedEnum;
use Capell\Admin\Filament\Concerns\HasConfiguredForm;
use Capell\Admin\Filament\Concerns\HasConfiguredTable;
use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Pages\CreateCampaignCtaWidget;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Pages\EditCampaignCtaWidget;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Pages\ListCampaignCtaWidgets;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Schemas\CampaignCtaWidgetForm;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Tables\CampaignCtaWidgetsTable;
use Capell\CampaignStudio\Models\CampaignCtaWidget;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class CampaignCtaWidgetResource extends Resource
{
    use HasConfiguredForm;
    use HasConfiguredTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CursorArrowRays;

    protected static ?string $recordTitleAttribute = 'name';

    /** @var class-string<CampaignCtaWidgetForm> */
    private static string $formConfigurator = CampaignCtaWidgetForm::class;

    /** @var class-string<CampaignCtaWidgetsTable> */
    private static string $tableConfigurator = CampaignCtaWidgetsTable::class;

    /** @return class-string<CampaignCtaWidgetForm> */
    public static function getFormConfigurator(): string
    {
        return self::$formConfigurator;
    }

    /** @return class-string<CampaignCtaWidgetsTable> */
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

    /** @return class-string<CampaignCtaWidget> */
    #[Override]
    public static function getModel(): string
    {
        return CampaignCtaWidget::class;
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
        return __('capell-campaign-studio::navigation.cta_widgets');
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
            'index' => ListCampaignCtaWidgets::route('/'),
            'create' => CreateCampaignCtaWidget::route('/create'),
            'edit' => EditCampaignCtaWidget::route('/{record}/edit'),
        ];
    }
}
