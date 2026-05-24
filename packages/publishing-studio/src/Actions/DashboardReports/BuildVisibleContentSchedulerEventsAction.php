<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions\DashboardReports;

use Capell\Admin\Support\SiteScope;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildVisibleContentSchedulerEventsAction
{
    use AsAction;

    /**
     * @return Collection<int, SchedulerEventData>
     */
    public function handle(
        ?SchedulerEventTypeEnum $eventType = null,
        ?string $sourceType = null,
        ?CarbonInterface $startsAt = null,
        ?CarbonInterface $endsAt = null,
        ?SchedulerEventStateEnum $state = null,
        int $limit = 250,
    ): Collection {
        $actor = auth()->user();
        $siteIds = null;

        if ($actor instanceof Authenticatable && ! SiteScope::isGlobalActor($actor)) {
            $siteIds = $actor->getAssignedSiteIds()->all();

            if ($siteIds === []) {
                return collect();
            }
        }

        $events = BuildContentSchedulerEventsAction::run(
            eventType: $eventType,
            sourceType: $sourceType,
            startsAt: $startsAt,
            endsAt: $endsAt,
            state: $state,
            siteIds: $siteIds,
            limit: $limit,
        );

        if (! $actor instanceof Authenticatable || SiteScope::isGlobalActor($actor)) {
            return $events;
        }

        return $events
            ->filter(function (SchedulerEventData $event) use ($actor): bool {
                if ($event->siteId === null) {
                    return false;
                }

                return $actor->getAssignedSiteIds()->contains($event->siteId);
            })
            ->values();
    }
}
