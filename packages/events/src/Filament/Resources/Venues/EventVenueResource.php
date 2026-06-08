<?php

declare(strict_types=1);

namespace Capell\Events\Filament\Resources\Venues;

use BackedEnum;
use Capell\Admin\Filament\Concerns\HasConfiguredForm;
use Capell\Admin\Filament\Concerns\HasConfiguredTable;
use Capell\Admin\Filament\Concerns\HasNavigationBadge;
use Capell\Admin\Support\SiteScope;
use Capell\Events\Filament\Resources\Venues\Pages\ManageEventVenues;
use Capell\Events\Models\EventVenue;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class EventVenueResource extends Resource
{
    use HasConfiguredForm;
    use HasConfiguredTable;
    use HasNavigationBadge;

    protected static ?string $slug = 'events/venues/event-venues';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function getModel(): string
    {
        return EventVenue::class;
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-events::generic.event_venues');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return (string) __('capell-admin::navigation.group_content');
    }

    #[Override]
    public static function getNavigationParentItem(): ?string
    {
        return (string) __('capell-events::generic.events');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('capell-events::table.name'))->required(),
            TextInput::make('line1')->label(__('capell-events::form.line1')),
            TextInput::make('line2')->label(__('capell-events::form.line2')),
            TextInput::make('city')->label(__('capell-events::form.city')),
            TextInput::make('postal_code')->label(__('capell-events::form.postal_code')),
            TextInput::make('map_url')->label(__('capell-events::form.map_url'))->url(),
        ])->columns();
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('capell-events::table.name'))->searchable(),
            TextColumn::make('full_address')->label(__('capell-events::table.address'))->wrap(),
        ]);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ManageEventVenues::route('/'),
        ];
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return SiteScope::applyForCurrentActor(parent::getEloquentQuery());
    }
}
