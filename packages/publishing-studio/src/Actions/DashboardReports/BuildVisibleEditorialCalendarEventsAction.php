<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions\DashboardReports;

use Capell\Admin\Support\SiteScope;
use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildVisibleEditorialCalendarEventsAction
{
    use AsAction;

    /**
     * @param  list<string>|null  $sourceTypes
     * @param  list<string>|null  $eventTypes
     * @return Collection<int, EditorialCalendarEventData>
     */
    public function handle(
        ?CarbonInterface $startsAt = null,
        ?CarbonInterface $endsAt = null,
        ?array $sourceTypes = null,
        ?array $eventTypes = null,
        ?string $state = null,
        int $limit = 250,
    ): Collection {
        $actor = auth()->user();
        $siteIds = null;

        if ($actor instanceof Authenticatable && ! SiteScope::isGlobalActor($actor)) {
            $siteIds = array_values($actor->getAssignedSiteIds()->all());

            if ($siteIds === []) {
                return collect();
            }
        }

        $events = BuildEditorialCalendarEventsAction::run(
            startsAt: $startsAt,
            endsAt: $endsAt,
            sourceTypes: $sourceTypes,
            eventTypes: $eventTypes,
            siteIds: $siteIds,
            state: $state,
            limit: $limit,
        );

        if (! $actor instanceof Authenticatable || SiteScope::isGlobalActor($actor)) {
            return $events;
        }

        return $events
            ->filter(function (EditorialCalendarEventData $event) use ($actor): bool {
                if ($event->siteId === null) {
                    return false;
                }

                return $actor->getAssignedSiteIds()->contains($event->siteId);
            })
            ->values();
    }
}
