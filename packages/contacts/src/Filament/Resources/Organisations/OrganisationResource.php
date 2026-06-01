<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Organisations;

use BackedEnum;
use Capell\Contacts\Filament\Resources\Organisations\Pages\ListOrganisations;
use Capell\Contacts\Models\Organisation;
use Capell\Contacts\Providers\ContactsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class OrganisationResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('capell-contacts::generic.fields.name'))->searchable()->sortable(),
            TextColumn::make('domain')->label(__('capell-contacts::generic.fields.domain'))->searchable(),
            TextColumn::make('website')->label(__('capell-contacts::generic.fields.website')),
            TextColumn::make('status')->label(__('capell-contacts::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('updated_at')->label(__('capell-contacts::generic.fields.updated_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return Organisation::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('site');
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
        return __('capell-contacts::generic.resources.organisations');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(ContactsServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListOrganisations::route('/'),
        ];
    }
}
