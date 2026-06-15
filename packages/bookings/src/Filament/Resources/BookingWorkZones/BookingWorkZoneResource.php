<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingWorkZones;

use BackedEnum;
use Capell\Bookings\Filament\Resources\BookingWorkZones\Pages\CreateBookingWorkZone;
use Capell\Bookings\Filament\Resources\BookingWorkZones\Pages\EditBookingWorkZone;
use Capell\Bookings\Filament\Resources\BookingWorkZones\Pages\ListBookingWorkZones;
use Capell\Bookings\Models\BookingWorkZone;
use Filament\Forms\Components\KeyValue;
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

final class BookingWorkZoneResource extends Resource
{
    protected static ?string $slug = 'bookings/work-zones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.work_zones'))
                ->schema([
                    TextInput::make('name')->label(__('capell-bookings::admin.fields.name'))->required()->maxLength(255),
                    TextInput::make('service_area')->label(__('capell-bookings::admin.fields.service_area'))->maxLength(255),
                    KeyValue::make('postal_code_prefixes')->label(__('capell-bookings::admin.fields.postal_code_prefixes'))->columnSpanFull(),
                    Toggle::make('active')->label(__('capell-bookings::admin.fields.active'))->default(true),
                ])->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('capell-bookings::admin.fields.name'))->searchable()->sortable(),
            TextColumn::make('service_area')->label(__('capell-bookings::admin.fields.service_area'))->searchable()->sortable(),
            IconColumn::make('active')->label(__('capell-bookings::admin.fields.active'))->boolean()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingWorkZone::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.work_zones');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingWorkZones::route('/'),
            'create' => CreateBookingWorkZone::route('/create'),
            'edit' => EditBookingWorkZone::route('/{record}/edit'),
        ];
    }
}
