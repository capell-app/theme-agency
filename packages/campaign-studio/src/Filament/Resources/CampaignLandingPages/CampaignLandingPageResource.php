<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignLandingPages;

use BackedEnum;
use Capell\Admin\Filament\Concerns\HasConfiguredForm;
use Capell\Admin\Filament\Concerns\HasConfiguredTable;
use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\Pages\CreateCampaignLandingPage;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\Pages\EditCampaignLandingPage;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\Pages\ListCampaignLandingPages;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\Schemas\CampaignLandingPageForm;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\Tables\CampaignLandingPagesTable;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class CampaignLandingPageResource extends Resource
{
    use HasConfiguredForm;
    use HasConfiguredTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentText;

    protected static ?string $recordTitleAttribute = 'headline';

    protected static string $formConfigurator = CampaignLandingPageForm::class;

    protected static string $tableConfigurator = CampaignLandingPagesTable::class;

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

    /** @return class-string<CampaignLandingPage> */
    #[Override]
    public static function getModel(): string
    {
        return CampaignLandingPage::class;
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
        return __('capell-campaign-studio::navigation.landing_pages');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::getPackage(CampaignStudioServiceProvider::$packageName)->isInstalled();
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCampaignLandingPages::route('/'),
            'create' => CreateCampaignLandingPage::route('/create'),
            'edit' => EditCampaignLandingPage::route('/{record}/edit'),
        ];
    }
}
