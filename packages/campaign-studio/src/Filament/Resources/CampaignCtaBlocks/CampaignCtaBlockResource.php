<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks;

use BackedEnum;
use Capell\Admin\Filament\Concerns\HasConfiguredForm;
use Capell\Admin\Filament\Concerns\HasConfiguredTable;
use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\Pages\CreateCampaignCtaBlock;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\Pages\EditCampaignCtaBlock;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\Pages\ListCampaignCtaBlocks;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\Schemas\CampaignCtaBlockForm;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\Tables\CampaignCtaBlocksTable;
use Capell\CampaignStudio\Models\CampaignCtaBlock;
use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class CampaignCtaBlockResource extends Resource
{
    use HasConfiguredForm;
    use HasConfiguredTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CursorArrowRays;

    protected static ?string $recordTitleAttribute = 'name';

    private static string $formConfigurator = CampaignCtaBlockForm::class;

    private static string $tableConfigurator = CampaignCtaBlocksTable::class;

    /** @return class-string<CampaignCtaBlockForm> */
    public static function getFormConfigurator(): string
    {
        return self::$formConfigurator;
    }

    /** @return class-string<CampaignCtaBlocksTable> */
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
        return SiteScope::applyForCurrentActor(parent::getEloquentQuery());
    }

    /** @return class-string<CampaignCtaBlock> */
    #[Override]
    public static function getModel(): string
    {
        return CampaignCtaBlock::class;
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
        return __('capell-campaign-studio::navigation.cta_blocks');
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
            'index' => ListCampaignCtaBlocks::route('/'),
            'create' => CreateCampaignCtaBlock::route('/create'),
            'edit' => EditCampaignCtaBlock::route('/{record}/edit'),
        ];
    }
}
