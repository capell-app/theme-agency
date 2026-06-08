<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Resources\PreviewLinks;

use BackedEnum;
use Capell\Admin\Filament\Concerns\HasConfiguredTable;
use Capell\PublishingStudio\Filament\Resources\PreviewLinks\Pages\ManagePreviewLinks;
use Capell\PublishingStudio\Filament\Resources\PreviewLinks\Tables\PreviewLinksTable;
use Capell\PublishingStudio\Models\PreviewLink;
use Capell\PublishingStudio\Support\WorkspaceAccess;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Override;

class PreviewLinkResource extends Resource
{
    use HasConfiguredTable;

    protected static ?string $slug = 'publishing-studio/preview-links';

    protected static ?string $model = PreviewLink::class;

    protected static string $tableConfigurator = PreviewLinksTable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Link;

    protected static ?int $navigationSort = 3;

    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['workspace', 'issuedBy'])
            ->whereHas(
                'workspace',
                fn (Builder $query): Builder => WorkspaceAccess::scopeVisibleTo($query, auth()->user()),
            );
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return static::getTableConfigurator()::configure($table);
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_workflow');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-admin::navigation.preview_links');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return (string) __('capell-admin::workspace.preview_link.singular');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return (string) __('capell-admin::workspace.preview_link.plural');
    }

    #[Override]
    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return static::$navigationIcon;
    }

    #[Override]
    public static function getActiveNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return static::$activeNavigationIcon;
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ManagePreviewLinks::route('/'),
        ];
    }
}
