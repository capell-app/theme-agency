<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions\DashboardReports;

use Capell\Core\Actions\GetEditPageResourceUrlAction;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\WorkspaceResource;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Support\WorkspaceSchema;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, SchedulerEventData> run(?SchedulerEventTypeEnum $eventType = null, ?string $sourceType = null, ?CarbonInterface $startsAt = null, ?CarbonInterface $endsAt = null, ?SchedulerEventStateEnum $state = null, ?int $siteId = null, array<array-key, mixed>|null $siteIds = null, ?int $ownerId = null, ?string $ownerType = null, int $limit = 250)
 */
final class BuildContentSchedulerEventsAction
{
    use AsAction;

    /**
     * @param  array<array-key, mixed>  $siteIds
     * @return Collection<int, SchedulerEventData>
     */
    public function handle(
        ?SchedulerEventTypeEnum $eventType = null,
        ?string $sourceType = null,
        ?CarbonInterface $startsAt = null,
        ?CarbonInterface $endsAt = null,
        ?SchedulerEventStateEnum $state = null,
        ?int $siteId = null,
        ?array $siteIds = null,
        ?int $ownerId = null,
        ?string $ownerType = null,
        int $limit = 250,
    ): Collection {
        $startsAt = $startsAt instanceof CarbonInterface ? CarbonImmutable::instance($startsAt) : CarbonImmutable::now();
        $endsAt = $endsAt instanceof CarbonInterface ? CarbonImmutable::instance($endsAt) : CarbonImmutable::now()->addMonths(6);
        $siteIds ??= $siteId !== null ? [$siteId] : null;

        $events = collect();

        if ($sourceType === null || $sourceType === 'page') {
            $events = $events->merge($this->pageEvents($eventType, $startsAt, $endsAt, $state, $siteIds, $ownerId, $ownerType, $limit));
        }

        if ($sourceType === null || $sourceType === 'workspace') {
            $events = $events->merge($this->workspaceEvents($eventType, $startsAt, $endsAt, $state, $siteIds, $ownerId, $ownerType, $limit));
            $events = $events->merge($this->legacyWorkspaceEvents($eventType, $startsAt, $endsAt, $state, $siteIds, $ownerId, $ownerType, $limit));
        }

        return $events
            ->unique(fn (SchedulerEventData $event): string => implode(':', [
                $event->sourceType,
                (string) $event->sourceId,
                $event->eventType->value,
                (string) $event->scheduledFor->getTimestamp(),
            ]))
            ->sortBy(fn (SchedulerEventData $event): int => $event->scheduledFor->getTimestamp())
            ->take($limit)
            ->values();
    }

    /**
     * @param  array<array-key, mixed>  $siteIds
     * @return Collection<int, SchedulerEventData>
     */
    private function pageEvents(
        ?SchedulerEventTypeEnum $eventType,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        ?SchedulerEventStateEnum $state,
        ?array $siteIds,
        ?int $ownerId,
        ?string $ownerType,
        int $limit,
    ): Collection {
        if (($state instanceof SchedulerEventStateEnum && $state !== SchedulerEventStateEnum::Scheduled)
            || $ownerId !== null
            || $ownerType !== null) {
            return collect();
        }

        $events = collect();

        if (! $eventType instanceof SchedulerEventTypeEnum || $eventType === SchedulerEventTypeEnum::Publish) {
            $events = $events->merge($this->pageColumnEvents('visible_from', SchedulerEventTypeEnum::Publish, $startsAt, $endsAt, $siteIds, $limit));
        }

        if (! $eventType instanceof SchedulerEventTypeEnum || $eventType === SchedulerEventTypeEnum::Unpublish) {
            return $events->merge($this->pageColumnEvents('visible_until', SchedulerEventTypeEnum::Unpublish, $startsAt, $endsAt, $siteIds, $limit));
        }

        return $events;
    }

    /**
     * @param  array<array-key, mixed>  $siteIds
     * @return Collection<int, SchedulerEventData>
     */
    private function pageColumnEvents(
        string $column,
        SchedulerEventTypeEnum $eventType,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        ?array $siteIds,
        int $limit,
    ): Collection {
        return Page::query()
            ->with(['type', 'site'])
            ->whereBetween($column, [$startsAt, $endsAt])
            ->when($siteIds !== null, fn (Builder $query): Builder => $query->whereIn('site_id', $siteIds))
            ->orderBy($column)
            ->limit($limit)
            ->get()
            ->map(function (Page $page) use ($column, $eventType): SchedulerEventData {
                $scheduledFor = $page->getAttribute($column);
                $recordUrl = GetEditPageResourceUrlAction::run($page);

                return new SchedulerEventData(
                    id: 'page-' . $page->id . '-' . $eventType->value,
                    sourceType: 'page',
                    sourceId: $page->id,
                    title: $page->name,
                    eventType: $eventType,
                    scheduledFor: CarbonImmutable::instance($scheduledFor),
                    status: __('capell-publishing-studio::scheduler.status.page_scheduled'),
                    description: (string) __('capell-publishing-studio::scheduler.descriptions.page_' . $eventType->value),
                    recordUrl: $recordUrl,
                    state: SchedulerEventStateEnum::Scheduled,
                    siteId: is_numeric($page->site_id) ? (int) $page->site_id : null,
                    siteName: $page->site?->name,
                    timezone: config('app.timezone', 'UTC'),
                );
            });
    }

    /**
     * @param  array<array-key, mixed>  $siteIds
     * @return Collection<int, SchedulerEventData>
     */
    private function workspaceEvents(
        ?SchedulerEventTypeEnum $eventType,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        ?SchedulerEventStateEnum $state,
        ?array $siteIds,
        ?int $ownerId,
        ?string $ownerType,
        int $limit,
    ): Collection {
        if (! WorkspaceSchema::hasTable('publishing_scheduler_events')) {
            return collect();
        }

        $query = SchedulerEvent::query()
            ->with(['workspace', 'owner'])
            ->whereBetween('scheduled_for', [$startsAt, $endsAt]);

        if ($eventType instanceof SchedulerEventTypeEnum) {
            $query->where('event_type', $eventType->value);
        }

        if ($state instanceof SchedulerEventStateEnum) {
            $query->where('state', $state->value);
        }

        return $query
            ->when($siteIds !== null, fn (Builder $query): Builder => $query->whereIn('site_id', $siteIds))
            ->when($ownerId !== null, fn (Builder $query): Builder => $query->where('owner_id', $ownerId))
            ->when($ownerType !== null, fn (Builder $query): Builder => $query->where('owner_type', $ownerType))
            ->orderBy('scheduled_for')
            ->limit($limit)
            ->get()
            ->map(fn (SchedulerEvent $event): SchedulerEventData => $this->workspaceEvent($event));
    }

    /**
     * @param  array<array-key, mixed>  $siteIds
     * @return Collection<int, SchedulerEventData>
     */
    private function legacyWorkspaceEvents(
        ?SchedulerEventTypeEnum $eventType,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        ?SchedulerEventStateEnum $state,
        ?array $siteIds,
        ?int $ownerId,
        ?string $ownerType,
        int $limit,
    ): Collection {
        if (! WorkspaceSchema::hasWorkspaceTable()
            || ($state instanceof SchedulerEventStateEnum && $state !== SchedulerEventStateEnum::Scheduled)
            || $siteIds !== null
            || $ownerId !== null
            || $ownerType !== null) {
            return collect();
        }

        $columns = [
            SchedulerEventTypeEnum::Publish->value => 'publish_at',
            SchedulerEventTypeEnum::Unpublish->value => 'unpublish_at',
            SchedulerEventTypeEnum::Embargo->value => 'embargo_until',
            SchedulerEventTypeEnum::ReviewReminder->value => 'review_reminder_at',
        ];

        $events = collect();

        foreach ($columns as $eventTypeValue => $column) {
            $currentEventType = SchedulerEventTypeEnum::from($eventTypeValue);

            if ($eventType instanceof SchedulerEventTypeEnum && $eventType !== $currentEventType) {
                continue;
            }

            $events = $events->merge(
                Workspace::query()
                    ->whereBetween($column, [$startsAt, $endsAt])
                    ->orderBy($column)
                    ->limit($limit)
                    ->get()
                    ->map(fn (Workspace $workspace): SchedulerEventData => $this->legacyWorkspaceEvent($workspace, $currentEventType, $column)),
            );
        }

        return $events;
    }

    private function legacyWorkspaceEvent(Workspace $workspace, SchedulerEventTypeEnum $eventType, string $column): SchedulerEventData
    {
        $recordUrl = Route::has('filament.admin.resources.publishing-studio.index')
            ? WorkspaceResource::getUrl('index', ['tableSearch' => $workspace->name])
            : null;

        return new SchedulerEventData(
            id: 'workspace-' . $workspace->id . '-' . $eventType->value,
            sourceType: 'workspace',
            sourceId: $workspace->id,
            title: $workspace->name,
            eventType: $eventType,
            scheduledFor: CarbonImmutable::instance($workspace->getAttribute($column)),
            status: $workspace->status->getLabel(),
            description: (string) __('capell-publishing-studio::scheduler.descriptions.workspace_' . $eventType->value),
            recordUrl: $recordUrl,
            state: SchedulerEventStateEnum::Scheduled,
            timezone: config('app.timezone', 'UTC'),
        );
    }

    private function workspaceEvent(SchedulerEvent $event): SchedulerEventData
    {
        $workspace = $event->workspace;
        $recordUrl = Route::has('filament.admin.resources.publishing-studio.index') && $workspace instanceof Workspace
            ? WorkspaceResource::getUrl('index', ['tableSearch' => $workspace->name])
            : null;

        return new SchedulerEventData(
            id: 'scheduler-event-' . $event->id,
            sourceType: 'workspace',
            sourceId: $event->source_id,
            title: $workspace->name ?? (string) __('capell-publishing-studio::scheduler.missing_workspace'),
            eventType: $event->event_type,
            scheduledFor: $event->scheduled_for,
            status: $event->state->getLabel(),
            description: (string) __('capell-publishing-studio::scheduler.descriptions.workspace_' . $event->event_type->value),
            recordUrl: $recordUrl,
            state: $event->state,
            siteId: is_numeric($event->site_id) ? (int) $event->site_id : null,
            ownerId: $event->owner_id,
            ownerName: $event->owner->name ?? null,
            timezone: $event->display_timezone,
            failure: $event->last_failure_message,
        );
    }
}
