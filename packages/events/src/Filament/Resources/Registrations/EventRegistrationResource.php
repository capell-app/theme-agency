<?php

declare(strict_types=1);

namespace Capell\Events\Filament\Resources\Registrations;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Events\Actions\UpdateRegistrationStatusAction;
use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Filament\Resources\Registrations\Pages\ManageEventRegistrations;
use Capell\Events\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Override;

class EventRegistrationResource extends Resource
{
    protected static ?string $slug = 'events/registrations/event-registrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    #[Override]
    public static function getModel(): string
    {
        return EventRegistration::class;
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-events::generic.event_registrations');
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
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('capell-events::table.name'))->searchable(),
                TextColumn::make('email')->label(__('capell-events::table.email'))->searchable(),
                TextColumn::make('occurrence.event.name')->label(__('capell-events::table.event')),
                TextColumn::make('status')->label(__('capell-events::table.status'))->badge(),
                TextColumn::make('quantity')->label(__('capell-events::table.quantity')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-events::table.status'))
                    ->options(EventRegistrationStatusEnum::class),
            ])
            ->recordActions([
                self::registrationStatusAction(
                    name: 'confirm',
                    status: EventRegistrationStatusEnum::Confirmed,
                    icon: 'heroicon-o-check-circle',
                    color: 'success',
                    successMessage: 'registration_confirmed',
                ),
                self::registrationStatusAction(
                    name: 'cancel',
                    status: EventRegistrationStatusEnum::Cancelled,
                    icon: 'heroicon-o-x-circle',
                    color: 'danger',
                    successMessage: 'registration_cancelled',
                ),
            ]);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ManageEventRegistrations::route('/'),
        ];
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('occurrence.event', fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query));
    }

    private static function registrationStatusAction(
        string $name,
        EventRegistrationStatusEnum $status,
        string $icon,
        string $color,
        string $successMessage,
    ): Action {
        return Action::make($name)
            ->label(__('capell-events::table.action_' . $name . '_registration'))
            ->icon($icon)
            ->color($color)
            ->authorize('update')
            ->visible(fn (EventRegistration $record): bool => $record->status !== $status)
            ->requiresConfirmation()
            ->action(function (EventRegistration $record) use ($name, $status, $successMessage): void {
                Gate::authorize('update', $record);

                UpdateRegistrationStatusAction::run($record, $status);

                Notification::make('event-registration-' . $name)
                    ->title(__('capell-events::table.' . $successMessage))
                    ->success()
                    ->send();
            });
    }
}
