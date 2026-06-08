<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Resources\Activities;

use BackedEnum;
use Capell\Contacts\Filament\Resources\Activities\Pages\ListContactActivities;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Providers\ContactsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ContactActivityResource extends Resource
{
    protected static ?string $slug = 'contacts/activities/contact-activities';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'summary';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('type')->label(__('capell-contacts::generic.fields.type'))->badge()->sortable(),
            TextColumn::make('contact.display_name')->label(__('capell-contacts::generic.resources.contact'))->searchable(),
            TextColumn::make('organisation.name')->label(__('capell-contacts::generic.resources.organisation'))->searchable(),
            TextColumn::make('lead.title')->label(__('capell-contacts::generic.resources.lead'))->searchable(),
            TextColumn::make('occurred_at')->label(__('capell-contacts::generic.fields.occurred_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return ContactActivity::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['contact', 'organisation', 'lead', 'site']);
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
        return __('capell-contacts::generic.resources.activities');
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
            'index' => ListContactActivities::route('/'),
        ];
    }
}
