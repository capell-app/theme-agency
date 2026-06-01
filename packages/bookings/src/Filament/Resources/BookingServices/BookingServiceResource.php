<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingServices;

use BackedEnum;
use Capell\Bookings\Filament\Resources\BookingServices\Pages\CreateBookingService;
use Capell\Bookings\Filament\Resources\BookingServices\Pages\EditBookingService;
use Capell\Bookings\Filament\Resources\BookingServices\Pages\ListBookingServices;
use Capell\Bookings\Models\BookingService;
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

final class BookingServiceResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-bookings::admin.resources.services'))
                ->schema([
                    TextInput::make('name')->label(__('capell-bookings::admin.fields.name'))->required()->maxLength(255),
                    Textarea::make('description')->label(__('capell-bookings::admin.fields.description'))->rows(3),
                    TextInput::make('duration_minutes')->label(__('capell-bookings::admin.fields.duration_minutes'))->numeric()->minValue(1)->required(),
                    TextInput::make('lead_time_minutes')->label(__('capell-bookings::admin.fields.lead_time_minutes'))->numeric()->minValue(0)->default(0),
                    TextInput::make('max_future_days')->label(__('capell-bookings::admin.fields.max_future_days'))->numeric()->minValue(1),
                    TextInput::make('buffer_before_minutes')->label(__('capell-bookings::admin.fields.buffer_before_minutes'))->numeric()->minValue(0)->default(0),
                    TextInput::make('buffer_after_minutes')->label(__('capell-bookings::admin.fields.buffer_after_minutes'))->numeric()->minValue(0)->default(0),
                    TextInput::make('color')->label(__('capell-bookings::admin.fields.color'))->maxLength(32),
                    Toggle::make('active')->label(__('capell-bookings::admin.fields.active'))->default(true),
                    Toggle::make('confirmation_required')->label(__('capell-bookings::admin.fields.confirmation_required'))->default(true),
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
            TextColumn::make('duration_minutes')->label(__('capell-bookings::admin.fields.duration_minutes'))->numeric()->sortable(),
            TextColumn::make('lead_time_minutes')->label(__('capell-bookings::admin.fields.lead_time_minutes'))->numeric()->sortable(),
            IconColumn::make('active')->label(__('capell-bookings::admin.fields.active'))->boolean()->sortable(),
            IconColumn::make('confirmation_required')->label(__('capell-bookings::admin.fields.confirmation_required'))->boolean()->sortable(),
            TextColumn::make('created_at')->label(__('capell-bookings::admin.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingService::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.services');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBookingServices::route('/'),
            'create' => CreateBookingService::route('/create'),
            'edit' => EditBookingService::route('/{record}/edit'),
        ];
    }
}
