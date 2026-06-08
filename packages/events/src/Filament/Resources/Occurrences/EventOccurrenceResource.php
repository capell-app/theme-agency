<?php

declare(strict_types=1);

namespace Capell\Events\Filament\Resources\Occurrences;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Events\Actions\CancelOccurrenceAction;
use Capell\Events\Actions\RescheduleOccurrenceAction;
use Capell\Events\Enums\EventOccurrenceStatusEnum;
use Capell\Events\Filament\Resources\Occurrences\Pages\ManageEventOccurrences;
use Capell\Events\Models\EventOccurrence;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Override;

class EventOccurrenceResource extends Resource
{
    protected static ?string $slug = 'events/occurrences/event-occurrences';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    #[Override]
    public static function getModel(): string
    {
        return EventOccurrence::class;
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-events::generic.event_occurrences');
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
                TextColumn::make('event.name')->label(__('capell-events::table.event'))->searchable(),
                TextColumn::make('starts_at')->label(__('capell-events::table.starts_at'))->dateTime()->sortable(),
                TextColumn::make('status')->label(__('capell-events::table.status'))->badge(),
                TextColumn::make('registration_count')->label(__('capell-events::table.registrations')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-events::table.status'))
                    ->options(EventOccurrenceStatusEnum::class),
            ])
            ->recordActions([
                self::rescheduleOccurrenceAction(),
                self::cancelOccurrenceAction(),
            ]);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ManageEventOccurrences::route('/'),
        ];
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('event', fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query));
    }

    private static function cancelOccurrenceAction(): Action
    {
        return Action::make('cancelOccurrence')
            ->label(__('capell-events::table.action_cancel_occurrence'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->authorize('update')
            ->visible(fn (EventOccurrence $record): bool => $record->status !== EventOccurrenceStatusEnum::Cancelled)
            ->requiresConfirmation()
            ->form([
                Textarea::make('reason')
                    ->label(__('capell-events::form.cancellation_reason'))
                    ->maxLength(500),
            ])
            ->action(function (EventOccurrence $record, array $data): void {
                Gate::authorize('update', $record);

                CancelOccurrenceAction::run(
                    $record,
                    is_string($data['reason'] ?? null) && trim($data['reason']) !== '' ? trim($data['reason']) : null,
                );

                Notification::make('event-occurrence-cancelled')
                    ->title(__('capell-events::table.occurrence_cancelled'))
                    ->success()
                    ->send();
            });
    }

    private static function rescheduleOccurrenceAction(): Action
    {
        return Action::make('rescheduleOccurrence')
            ->label(__('capell-events::table.action_reschedule_occurrence'))
            ->icon('heroicon-o-clock')
            ->color('warning')
            ->authorize('update')
            ->form([
                DateTimePicker::make('starts_at')
                    ->label(__('capell-events::form.starts_at'))
                    ->required()
                    ->seconds(false),
                DateTimePicker::make('ends_at')
                    ->label(__('capell-events::form.ends_at'))
                    ->seconds(false),
            ])
            ->fillForm(fn (EventOccurrence $record): array => [
                'starts_at' => $record->starts_at,
                'ends_at' => $record->ends_at,
            ])
            ->action(function (EventOccurrence $record, array $data): void {
                Gate::authorize('update', $record);

                RescheduleOccurrenceAction::run(
                    $record,
                    self::carbonFromActionValue($data['starts_at'] ?? null, $record->timezone),
                    self::nullableCarbonFromActionValue($data['ends_at'] ?? null, $record->timezone),
                );

                Notification::make('event-occurrence-rescheduled')
                    ->title(__('capell-events::table.occurrence_rescheduled'))
                    ->success()
                    ->send();
            });
    }

    private static function nullableCarbonFromActionValue(mixed $value, string $timezone): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::carbonFromActionValue($value, $timezone);
    }

    private static function carbonFromActionValue(mixed $value, string $timezone): CarbonImmutable
    {
        if ($value instanceof CarbonImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        return CarbonImmutable::parse((string) $value, $timezone);
    }
}
