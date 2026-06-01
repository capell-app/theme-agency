<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Actions\DashboardReports\BuildContentSchedulerEventsAction;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildEditorialCalendarEventsAction
{
    use AsAction;

    /**
     * @param  list<string>|null  $sourceTypes
     * @param  list<string>|null  $eventTypes
     * @param  list<int>|null  $siteIds
     * @return Collection<int, EditorialCalendarEventData>
     */
    public function handle(
        ?CarbonInterface $startsAt = null,
        ?CarbonInterface $endsAt = null,
        ?array $sourceTypes = null,
        ?array $eventTypes = null,
        ?int $siteId = null,
        ?array $siteIds = null,
        ?int $ownerId = null,
        ?string $ownerType = null,
        ?string $state = null,
        int $limit = 250,
    ): Collection {
        $normalizedSiteIds = $this->normalizeSiteIds($siteId, $siteIds);
        $normalizedLimit = max(1, min($limit, 1000));

        $query = new EditorialCalendarQueryData(
            startsAt: $startsAt instanceof CarbonInterface ? CarbonImmutable::instance($startsAt) : CarbonImmutable::now(),
            endsAt: $endsAt instanceof CarbonInterface ? CarbonImmutable::instance($endsAt) : CarbonImmutable::now()->addMonths(6),
            sourceTypes: $this->normalizeStrings($sourceTypes),
            eventTypes: $this->normalizeStrings($eventTypes),
            siteIds: $normalizedSiteIds,
            ownerId: $ownerId,
            ownerType: $ownerType,
            state: $state,
            limit: $normalizedLimit,
        );

        return $this->schedulerEvents($query)
            ->merge($this->contributedEvents($query))
            ->filter(fn (EditorialCalendarEventData $event): bool => $this->eventMatchesQuery($event, $query))
            ->unique(fn (EditorialCalendarEventData $event): string => implode(':', [
                $event->sourcePackage,
                $event->sourceType,
                $event->sourceId,
                $event->eventType,
                (string) $event->startsAt->getTimestamp(),
            ]))
            ->sortBy([
                fn (EditorialCalendarEventData $event): int => $event->startsAt->getTimestamp(),
                fn (EditorialCalendarEventData $event): string => $event->title,
            ])
            ->take($query->limit)
            ->values();
    }

    /**
     * @return Collection<int, EditorialCalendarEventData>
     */
    private function schedulerEvents(EditorialCalendarQueryData $query): Collection
    {
        if (! $this->includesAnySourceType($query, ['page', 'workspace'])) {
            return collect();
        }

        return BuildContentSchedulerEventsAction::run(
            eventType: $this->onlySchedulerEventType($query->eventTypes),
            sourceType: $this->onlySchedulerSourceType($query->sourceTypes),
            startsAt: $query->startsAt,
            endsAt: $query->endsAt,
            state: $this->schedulerState($query->state),
            siteIds: $query->siteIds,
            ownerId: $query->ownerId,
            ownerType: $query->ownerType,
            limit: $query->limit,
        )->map(fn (SchedulerEventData $event): EditorialCalendarEventData => $this->fromSchedulerEvent($event));
    }

    /**
     * @return Collection<int, EditorialCalendarEventData>
     */
    private function contributedEvents(EditorialCalendarQueryData $query): Collection
    {
        $events = collect();

        foreach (app()->tagged(EditorialCalendarEventContributor::TAG) as $contributor) {
            if (! $contributor instanceof EditorialCalendarEventContributor) {
                continue;
            }

            foreach ($contributor->editorialCalendarEvents($query) as $event) {
                $events->push($event);
            }
        }

        return $events;
    }

    private function fromSchedulerEvent(SchedulerEventData $event): EditorialCalendarEventData
    {
        return new EditorialCalendarEventData(
            id: 'scheduler:' . $event->id,
            sourcePackage: 'capell-app/publishing-studio',
            sourceType: $event->sourceType,
            sourceId: (string) $event->sourceId,
            title: $event->title,
            eventType: $event->eventType->value,
            startsAt: $event->scheduledFor,
            eventTypeLabel: $event->eventType->getLabel(),
            status: $event->status,
            description: $event->description,
            recordUrl: $event->recordUrl,
            state: $event->state?->value,
            siteId: $event->siteId,
            siteName: $event->siteName,
            ownerId: $event->ownerId,
            ownerName: $event->ownerName,
            timezone: $event->timezone,
            color: $event->eventType->getColor(),
            metadata: [
                'scheduler_event_id' => $event->id,
                'failure' => $event->failure,
            ],
        );
    }

    private function eventMatchesQuery(EditorialCalendarEventData $event, EditorialCalendarQueryData $query): bool
    {
        if (! $this->eventOverlapsRange($event, $query)) {
            return false;
        }

        if ($query->sourceTypes !== null && ! in_array($event->sourceType, $query->sourceTypes, true)) {
            return false;
        }

        if ($query->eventTypes !== null && ! in_array($event->eventType, $query->eventTypes, true)) {
            return false;
        }

        if ($query->siteIds !== null && ($event->siteId === null || ! in_array($event->siteId, $query->siteIds, true))) {
            return false;
        }

        if ($query->ownerId !== null && $event->ownerId !== $query->ownerId) {
            return false;
        }

        if ($query->state !== null && $event->state !== $query->state) {
            return false;
        }

        return true;
    }

    private function eventOverlapsRange(EditorialCalendarEventData $event, EditorialCalendarQueryData $query): bool
    {
        $eventEndsAt = $event->endsAt ?? $event->startsAt;

        return $event->startsAt <= $query->endsAt && $eventEndsAt >= $query->startsAt;
    }

    /**
     * @param  list<string>|null  $sourceTypes
     */
    private function onlySchedulerSourceType(?array $sourceTypes): ?string
    {
        if ($sourceTypes === null) {
            return null;
        }

        $schedulerSourceTypes = array_values(
            array_filter($sourceTypes, fn (string $sourceType): bool => in_array($sourceType, ['page', 'workspace'], true)),
        );

        return count($schedulerSourceTypes) === 1 ? $schedulerSourceTypes[0] : null;
    }

    /**
     * @param  list<string>|null  $eventTypes
     */
    private function onlySchedulerEventType(?array $eventTypes): ?SchedulerEventTypeEnum
    {
        if ($eventTypes === null || count($eventTypes) !== 1) {
            return null;
        }

        return SchedulerEventTypeEnum::tryFrom($eventTypes[0]);
    }

    private function schedulerState(?string $state): ?SchedulerEventStateEnum
    {
        return is_string($state) ? SchedulerEventStateEnum::tryFrom($state) : null;
    }

    /**
     * @param  list<string>  $sourceTypes
     */
    private function includesAnySourceType(EditorialCalendarQueryData $query, array $sourceTypes): bool
    {
        if ($query->sourceTypes === null) {
            return true;
        }

        foreach ($sourceTypes as $sourceType) {
            if (in_array($sourceType, $query->sourceTypes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>|null  $values
     * @return list<string>|null
     */
    private function normalizeStrings(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return array_values(collect($values)
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->all());
    }

    /**
     * @param  list<int>|null  $siteIds
     * @return list<int>|null
     */
    private function normalizeSiteIds(?int $siteId, ?array $siteIds): ?array
    {
        if ($siteIds === null) {
            return $siteId === null ? null : [$siteId];
        }

        if ($siteId !== null) {
            $siteIds[] = $siteId;
        }

        $normalizedSiteIds = collect($siteIds)
            ->map(fn (int $currentSiteId): int => $currentSiteId)
            ->unique()
            ->values()
            ->all();

        return $normalizedSiteIds === [] ? null : array_values($normalizedSiteIds);
    }
}
