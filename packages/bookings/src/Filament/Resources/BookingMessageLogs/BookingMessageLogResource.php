<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingMessageLogs;

use BackedEnum;
use Capell\Bookings\Actions\RetryBookingMessageAction;
use Capell\Bookings\Enums\BookingMessageStatusEnum;
use Capell\Bookings\Filament\Resources\BookingMessageLogs\Pages\ListBookingMessageLogs;
use Capell\Bookings\Models\BookingMessageLog;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
        ])->recordActions([
            self::retryRecordAction(),
        ]);
    }

    public static function retryRecordAction(): Action
    {
        return Action::make('retry')
            ->label(__('capell-bookings::admin.actions.retry_message'))
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->authorize('update')
            ->visible(fn (BookingMessageLog $record): bool => $record->status === BookingMessageStatusEnum::Failed)
            ->requiresConfirmation()
            ->action(function (BookingMessageLog $record): void {
                RetryBookingMessageAction::run($record);

                Notification::make('booking-message-retried')
                    ->title(__('capell-bookings::admin.messages.message_retried'))
                    ->success()
                    ->send();
            });
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
