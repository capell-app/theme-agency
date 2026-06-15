<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingDayPlanner;

use BackedEnum;
use Capell\Bookings\Filament\Resources\BookingDayPlanner\Pages\ListBookingDayPlanner;
use Capell\Bookings\Models\AppointmentRequest;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingDayPlannerResource extends Resource
{
    protected static ?string $slug = 'bookings/day-planner';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('requested_starts_at')->label(__('capell-bookings::admin.fields.requested_starts_at'))->dateTime()->sortable(),
            TextColumn::make('customer_name')->label(__('capell-bookings::admin.fields.customer_name'))->searchable(),
            TextColumn::make('staffMember.display_name')->label(__('capell-bookings::admin.fields.staff_member'))->searchable(),
            TextColumn::make('location.name')->label(__('capell-bookings::admin.fields.location'))->searchable(),
            TextColumn::make('travel_duration_minutes')->label(__('capell-bookings::admin.fields.travel_duration_minutes'))->numeric()->sortable(),
            TextColumn::make('fuel_allowance_pence')->label(__('capell-bookings::admin.fields.fuel_allowance_pence'))->money('GBP')->sortable(),
            IconColumn::make('is_time_pinned')->label(__('capell-bookings::admin.fields.is_time_pinned'))->boolean(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return AppointmentRequest::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.day_planner');
    }

    #[Override]
    public static function getPages(): array
    {
        return ['index' => ListBookingDayPlanner::route('/')];
    }
}
