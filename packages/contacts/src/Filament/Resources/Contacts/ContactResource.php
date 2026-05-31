<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Contacts;

use BackedEnum;
use Capell\Contacts\Filament\Resources\Contacts\Pages\ListContacts;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Providers\ContactsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ContactResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'display_name';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('display_name')->label(__('capell-contacts::generic.fields.display_name'))->searchable(),
            TextColumn::make('email')->label(__('capell-contacts::generic.fields.email'))->searchable(),
            TextColumn::make('phone')->label(__('capell-contacts::generic.fields.phone')),
            TextColumn::make('status')->label(__('capell-contacts::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('last_seen_at')->label(__('capell-contacts::generic.fields.last_seen_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return Contact::class;
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
        return __('capell-contacts::generic.resources.contacts');
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
            'index' => ListContacts::route('/'),
        ];
    }
}
