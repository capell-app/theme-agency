<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingTravelObservations;

use BackedEnum;
use Capell\Bookings\Filament\Resources\BookingTravelObservations\Pages\ListBookingTravelObservations;
use Capell\Bookings\Models\BookingTravelObservation;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingTravelObservationResource extends Resource
{
    protected static ?string $slug = 'bookings/travel-observations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('originLocation.name')->label(__('capell-bookings::admin.fields.origin'))->searchable(),
            TextColumn::make('destinationLocation.name')->label(__('capell-bookings::admin.fields.destination'))->searchable(),
            TextColumn::make('duration_minutes')->label(__('capell-bookings::admin.fields.duration_minutes'))->numeric()->sortable(),
            TextColumn::make('distance_miles')->label(__('capell-bookings::admin.fields.distance_miles'))->numeric()->sortable(),
            TextColumn::make('observed_at')->label(__('capell-bookings::admin.fields.observed_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingTravelObservation::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.travel_observations');
    }

    #[Override]
    public static function getPages(): array
    {
        return ['index' => ListBookingTravelObservations::route('/')];
    }
}
