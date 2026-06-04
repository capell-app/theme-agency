<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Pages\Tables;

use Capell\Admin\Filament\Contracts\TableConfigurator;
use Capell\Admin\Support\SiteScope;
use Capell\PublishingStudio\Actions\CancelSchedulerEventAction;
use Capell\PublishingStudio\Actions\DashboardReports\BuildVisibleContentSchedulerEventsAction;
use Capell\PublishingStudio\Actions\ExecuteSchedulerEventAction;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Workspace;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;

class ScheduledPublishingTable implements TableConfigurator
{
    public static function configure(Table $table): Table
    {
        return $table
            ->records(fn (
                ?array $filters = null,
                ?string $search = null,
                ?string $sortColumn = null,
                ?string $sortDirection = null,
            ): Collection => self::records($filters ?? [], $search, $sortColumn, $sortDirection))
            ->columns([
                TextColumn::make('title')
                    ->label(__('capell-admin::table.name'))
                    ->size('sm')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_type_label')
                    ->label(__('capell-publishing-studio::scheduler.table.event_type'))
                    ->size('sm')
                    ->badge()
                    ->color(fn (array $record): string => $record['event_type_color'] ?? 'gray'),
                TextColumn::make('source_type')
                    ->label(__('capell-publishing-studio::scheduler.table.source'))
                    ->size('sm')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => (string) __('capell-publishing-studio::scheduler.sources.' . $state)),
                TextColumn::make('status')
                    ->label(__('capell-admin::table.status'))
                    ->size('sm')
                    ->badge()
                    ->color(fn (array $record): string => $record['state_color'] ?? 'gray'),
                TextColumn::make('scheduled_for')
                    ->label(__('capell-publishing-studio::scheduler.table.scheduled_for'))
                    ->size('sm')
                    ->dateTime(),
                TextColumn::make('description')
                    ->label(__('capell-publishing-studio::scheduler.table.description'))
                    ->size('sm')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('failure')
                    ->label(__('capell-publishing-studio::scheduler.table.failure'))
                    ->size('sm')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('timezone')
                    ->label(__('capell-publishing-studio::scheduler.table.timezone'))
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label(__('capell-publishing-studio::scheduler.filters.event_type'))
                    ->options(SchedulerEventTypeEnum::class),
                SelectFilter::make('source_type')
                    ->label(__('capell-publishing-studio::scheduler.filters.source'))
                    ->options([
                        'workspace' => __('capell-publishing-studio::scheduler.sources.workspace'),
                        'page' => __('capell-publishing-studio::scheduler.sources.page'),
                    ]),
                SelectFilter::make('state')
                    ->label(__('capell-publishing-studio::scheduler.filters.state'))
                    ->options(SchedulerEventStateEnum::class),
                SelectFilter::make('quick')
                    ->label(__('capell-publishing-studio::scheduler.filters.quick'))
                    ->options([
                        'today' => __('capell-publishing-studio::scheduler.quick_filters.today'),
                        'next_7_days' => __('capell-publishing-studio::scheduler.quick_filters.next_7_days'),
                        'failed' => __('capell-publishing-studio::scheduler.quick_filters.failed'),
                        'blocked' => __('capell-publishing-studio::scheduler.quick_filters.blocked'),
                        'automatic_unpublishes' => __('capell-publishing-studio::scheduler.quick_filters.automatic_unpublishes'),
                        'review_reminders_due' => __('capell-publishing-studio::scheduler.quick_filters.review_reminders_due'),
                    ]),
            ])
            ->recordActions([
                Action::make('details')
                    ->label(__('capell-publishing-studio::scheduler.actions.details'))
                    ->modalHeading(fn (array $record): string => (string) $record['title'])
                    ->modalContent(fn (array $record): HtmlString => self::details($record))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('capell-publishing-studio::scheduler.actions.close')),
                Action::make('retry')
                    ->label(__('capell-publishing-studio::scheduler.actions.retry'))
                    ->visible(fn (array $record): bool => ($record['state'] ?? null) === SchedulerEventStateEnum::Failed->value
                        && self::canPublishEvent($record))
                    ->authorize(fn (array $record): bool => self::canPublishEvent($record))
                    ->action(function (array $record): void {
                        $event = self::eventFromRecord($record);

                        if (! $event instanceof SchedulerEvent) {
                            return;
                        }

                        self::authorizePublishEvent($event);

                        $event->state = SchedulerEventStateEnum::Scheduled;
                        $event->claimed_at = null;
                        $event->save();

                        ExecuteSchedulerEventAction::run($event);
                        $event->refresh();

                        if ($event->state === SchedulerEventStateEnum::Executed) {
                            Notification::make()
                                ->title(__('capell-publishing-studio::scheduler.notifications.retry_succeeded'))
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('capell-publishing-studio::scheduler.notifications.retry_failed'))
                            ->body($event->last_failure_message)
                            ->danger()
                            ->send();
                    }),
                Action::make('cancel')
                    ->label(__('capell-publishing-studio::scheduler.actions.cancel'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (array $record): bool => ($record['state'] ?? null) === SchedulerEventStateEnum::Scheduled->value
                        && str_starts_with((string) ($record['id'] ?? ''), 'scheduler-event-')
                        && self::canPublishEvent($record))
                    ->authorize(fn (array $record): bool => self::canPublishEvent($record))
                    ->action(function (array $record): void {
                        $event = self::eventFromRecord($record);

                        if (! $event instanceof SchedulerEvent) {
                            return;
                        }

                        self::authorizePublishEvent($event);

                        CancelSchedulerEventAction::run($event, auth()->user());

                        Notification::make()
                            ->title(__('capell-publishing-studio::scheduler.notifications.cancelled'))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordUrl(fn (array $record): ?string => $record['record_url'] ?? null)
            ->defaultSort(column: 'scheduled_for', direction: 'asc')
            ->paginated([10, 25, 50]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private static function records(
        array $filters,
        ?string $search,
        ?string $sortColumn,
        ?string $sortDirection,
    ): Collection {
        $eventType = self::filterValue($filters, 'event_type');
        $sourceType = self::filterValue($filters, 'source_type');
        $stateValue = self::filterValue($filters, 'state');
        $quick = self::filterValue($filters, 'quick');

        $startsAt = now()->subMonth();
        $endsAt = now()->addMonths(6);

        if ($quick === 'today') {
            $startsAt = today();
            $endsAt = now()->endOfDay();
        } elseif ($quick === 'next_7_days') {
            $startsAt = today();
            $endsAt = now()->addDays(7)->endOfDay();
        } elseif ($quick === 'failed' || $quick === 'blocked') {
            $startsAt = now()->subYears(5);
            $endsAt = now()->addMonths(6);
        }

        $records = BuildVisibleContentSchedulerEventsAction::run(
            eventType: $eventType !== null ? SchedulerEventTypeEnum::tryFrom($eventType) : null,
            sourceType: $sourceType,
            startsAt: $startsAt,
            endsAt: $endsAt,
            state: $stateValue !== null ? SchedulerEventStateEnum::tryFrom($stateValue) : null,
        )->map(fn (SchedulerEventData $event): array => $event->toTableRecord());

        if ($quick === 'failed') {
            $records = $records->filter(fn (array $record): bool => ($record['state'] ?? null) === SchedulerEventStateEnum::Failed->value);
        } elseif ($quick === 'blocked') {
            $blockedStates = [
                SchedulerEventStateEnum::SkippedEmbargo->value,
                SchedulerEventStateEnum::SkippedReleaseWindow->value,
                SchedulerEventStateEnum::SkippedStale->value,
            ];
            $records = $records->filter(fn (array $record): bool => in_array($record['state'] ?? null, $blockedStates, true));
        } elseif ($quick === 'automatic_unpublishes') {
            $records = $records->filter(fn (array $record): bool => ($record['event_type'] ?? null) === SchedulerEventTypeEnum::Unpublish->value);
        } elseif ($quick === 'review_reminders_due') {
            $records = $records->filter(fn (array $record): bool => ($record['event_type'] ?? null) === SchedulerEventTypeEnum::ReviewReminder->value);
        }

        if (is_string($search) && $search !== '') {
            $needle = mb_strtolower($search);

            $records = $records->filter(
                fn (array $record): bool => str_contains(mb_strtolower((string) $record['title']), $needle)
                    || str_contains(mb_strtolower((string) $record['event_type_label']), $needle)
                    || str_contains(mb_strtolower((string) $record['source_type']), $needle)
                    || str_contains(mb_strtolower((string) $record['status']), $needle)
                    || str_contains(mb_strtolower((string) ($record['description'] ?? '')), $needle),
            );
        }

        $sortColumn ??= 'scheduled_for';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

        $records = $records->sortBy(
            fn (array $record): mixed => self::sortValue($record, $sortColumn),
            descending: $sortDirection === 'desc',
        );

        return $records->values();
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private static function sortValue(array $record, string $sortColumn): mixed
    {
        return match ($sortColumn) {
            'title',
            'event_type_label',
            'source_type',
            'status',
            'state_label',
            'failure',
            'description' => mb_strtolower((string) ($record[$sortColumn] ?? '')),
            'scheduled_for' => $record['scheduled_for'],
            default => $record['scheduled_for'],
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private static function filterValue(array $filters, string $key): ?string
    {
        $value = $filters[$key]['value'] ?? $filters[$key] ?? null;

        if (! is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private static function eventFromRecord(array $record): ?SchedulerEvent
    {
        $id = (string) ($record['id'] ?? '');

        if (! str_starts_with($id, 'scheduler-event-')) {
            return null;
        }

        $event = SchedulerEvent::query()->find((int) str_replace('scheduler-event-', '', $id));

        return $event instanceof SchedulerEvent && self::canUseSite($event->site_id) ? $event : null;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private static function canPublishEvent(array $record): bool
    {
        $event = self::eventFromRecord($record);

        if (! $event instanceof SchedulerEvent) {
            return false;
        }

        $workspace = $event->workspace;

        return $workspace instanceof Workspace && Gate::allows('publish', $workspace);
    }

    private static function authorizePublishEvent(SchedulerEvent $event): void
    {
        $workspace = $event->workspace;

        throw_unless($workspace instanceof Workspace, AuthorizationException::class);

        Gate::authorize('publish', $workspace);
    }

    private static function canUseSite(?int $siteId): bool
    {
        $actor = auth()->user();

        if (! $actor instanceof Authenticatable || SiteScope::isGlobalActor($actor)) {
            return true;
        }

        return $siteId !== null && $actor->getAssignedSiteIds()->contains($siteId);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private static function details(array $record): HtmlString
    {
        $lines = [
            __('capell-publishing-studio::scheduler.table.event_type') . ': ' . ($record['event_type_label'] ?? ''),
            __('capell-admin::table.status') . ': ' . ($record['state_label'] ?? $record['status'] ?? ''),
            __('capell-publishing-studio::scheduler.table.scheduled_for') . ': ' . (($record['scheduled_for'] ?? null)?->toDateTimeString() ?? ''),
            __('capell-publishing-studio::scheduler.table.timezone') . ': ' . ($record['timezone'] ?? ''),
        ];

        if (($record['failure'] ?? null) !== null) {
            $lines[] = __('capell-publishing-studio::scheduler.table.failure') . ': ' . $record['failure'];
        }

        return new HtmlString('<div aria-live="polite" class="space-y-2 text-sm"><p>' . implode('</p><p>', array_map(e(...), $lines)) . '</p></div>');
    }
}
