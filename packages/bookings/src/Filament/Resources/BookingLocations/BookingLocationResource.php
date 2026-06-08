<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingLocations;

use BackedEnum;
use Capell\Bookings\Enums\BookingLocationTypeEnum;
use Capell\Bookings\Filament\Resources\BookingLocations\Pages\CreateBookingLocation;
use Capell\Bookings\Filament\Resources\BookingLocations\Pages\EditBookingLocation;
use Capell\Bookings\Filament\Resources\BookingLocations\Pages\ListBookingLocations;
use Capell\Bookings\Models\BookingLocation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingLocationResource extends Resource
{
    protected static ?string $slug = 'bookings/booking-locations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.locations'))
                ->schema([
                    TextInput::make('name')->label(__('capell-bookings::admin.fields.name'))->required()->maxLength(255),
                    Select::make('type')->label(__('capell-bookings::admin.fields.type'))->options(self::typeOptions())->required(),
                    TextInput::make('timezone')->label(__('capell-bookings::admin.fields.timezone'))->required()->default('UTC')->maxLength(64),
                    TextInput::make('virtual_url')->label(__('capell-bookings::admin.fields.virtual_url'))->url()->maxLength(2048),
                    TextInput::make('line1')->label(__('capell-bookings::admin.fields.line1'))->maxLength(255),
                    TextInput::make('line2')->label(__('capell-bookings::admin.fields.line2'))->maxLength(255),
                    TextInput::make('city')->label(__('capell-bookings::admin.fields.city'))->maxLength(255),
                    TextInput::make('state')->label(__('capell-bookings::admin.fields.state'))->maxLength(255),
                    TextInput::make('postal_code')->label(__('capell-bookings::admin.fields.postal_code'))->maxLength(255),
                    TextInput::make('country')->label(__('capell-bookings::admin.fields.country'))->maxLength(2),
                    Toggle::make('active')->label(__('capell-bookings::admin.fields.active'))->default(true),
                    Textarea::make('instructions')->label(__('capell-bookings::admin.fields.instructions'))->rows(3)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('capell-bookings::admin.fields.name'))->searchable()->sortable(),
            TextColumn::make('type')->label(__('capell-bookings::admin.fields.type'))->badge()->sortable(),
            TextColumn::make('city')->label(__('capell-bookings::admin.fields.city'))->searchable()->toggleable(),
            TextColumn::make('timezone')->label(__('capell-bookings::admin.fields.timezone'))->sortable(),
            IconColumn::make('active')->label(__('capell-bookings::admin.fields.active'))->boolean()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingLocation::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.locations');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingLocations::route('/'),
            'create' => CreateBookingLocation::route('/create'),
            'edit' => EditBookingLocation::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function typeOptions(): array
    {
        return collect(BookingLocationTypeEnum::cases())
            ->mapWithKeys(static fn (BookingLocationTypeEnum $type): array => [$type->value => $type->getLabel()])
            ->all();
    }
}
