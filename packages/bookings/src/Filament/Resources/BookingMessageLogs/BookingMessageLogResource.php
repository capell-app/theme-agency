<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingMessageLogs;

use BackedEnum;
use Capell\Bookings\Filament\Resources\BookingMessageLogs\Pages\ListBookingMessageLogs;
use Capell\Bookings\Models\BookingMessageLog;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class BookingMessageLogResource extends Resource
{
    protected static ?string $slug = 'bookings/message-logs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('channel')->label(__('capell-bookings::admin.fields.channel'))->badge()->sortable(),
            TextColumn::make('type')->label(__('capell-bookings::admin.fields.type'))->searchable()->sortable(),
            TextColumn::make('status')->label(__('capell-bookings::admin.fields.status'))->badge()->sortable(),
            TextColumn::make('recipient')->label(__('capell-bookings::admin.fields.recipient'))->searchable()->toggleable(),
            TextColumn::make('sent_at')->label(__('capell-bookings::admin.fields.sent_at'))->dateTime()->sortable(),
            TextColumn::make('created_at')->label(__('capell-bookings::admin.fields.created_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return BookingMessageLog::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-bookings::admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-bookings::admin.resources.message_logs');
    }

    #[Override]
    public static function getPages(): array
    {
        return ['index' => ListBookingMessageLogs::route('/')];
    }
}
