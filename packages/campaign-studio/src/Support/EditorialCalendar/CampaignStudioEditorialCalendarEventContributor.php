<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Support\EditorialCalendar;

use Capell\CampaignStudio\Filament\Resources\CampaignGroups\CampaignGroupResource;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

final class CampaignStudioEditorialCalendarEventContributor implements EditorialCalendarEventContributor
{
    private const string START_EVENT_TYPE = 'campaign.start';

    private const string END_EVENT_TYPE = 'campaign.end';

    /**
     * @return Collection<int, EditorialCalendarEventData>
     */
    public function editorialCalendarEvents(EditorialCalendarQueryData $query): Collection
    {
        if (! $this->shouldContribute($query)) {
            return collect();
        }

        $events = collect();

        if ($this->includesEventType($query, self::START_EVENT_TYPE)) {
            $events = $events->merge($this->campaignColumnEvents($query, 'starts_at', self::START_EVENT_TYPE));
        }

        if ($this->includesEventType($query, self::END_EVENT_TYPE)) {
            return $events->merge($this->campaignColumnEvents($query, 'ends_at', self::END_EVENT_TYPE));
        }

        return $events;
    }

    private function shouldContribute(EditorialCalendarQueryData $query): bool
    {
        if ($query->sourceTypes !== null && ! in_array('campaign', $query->sourceTypes, true)) {
            return false;
        }

        return $query->ownerId === null && $query->ownerType === null;
    }

    private function includesEventType(EditorialCalendarQueryData $query, string $eventType): bool
    {
        return $query->eventTypes === null || in_array($eventType, $query->eventTypes, true);
    }

    /**
     * @return Collection<int, EditorialCalendarEventData>
     */
    private function campaignColumnEvents(EditorialCalendarQueryData $query, string $column, string $eventType): Collection
    {
        return CampaignGroup::query()
            ->with('site')
            ->whereBetween($column, [$query->startsAt, $query->endsAt])
            ->when($query->state !== null, fn (Builder $builder): Builder => $builder->where('status', $query->state))
            ->when($query->siteIds !== null, function (Builder $builder) use ($query): Builder {
                return $builder->where(function (Builder $siteBuilder) use ($query): void {
                    $siteBuilder
                        ->whereNull('site_id')
                        ->orWhereIn('site_id', $query->siteIds);
                });
            })
            ->orderBy($column)
            ->limit($query->limit)
            ->get()
            ->map(fn (CampaignGroup $campaign): EditorialCalendarEventData => $this->campaignEvent($campaign, $column, $eventType));
    }

    private function campaignEvent(CampaignGroup $campaign, string $column, string $eventType): EditorialCalendarEventData
    {
        $startsAt = $campaign->getAttribute($column);

        return new EditorialCalendarEventData(
            id: 'campaign-' . $campaign->id . '-' . str_replace('campaign.', '', $eventType),
            sourcePackage: CampaignStudioServiceProvider::$packageName,
            sourceType: 'campaign',
            sourceId: (string) $campaign->getKey(),
            title: $campaign->name,
            eventType: $eventType,
            startsAt: $startsAt instanceof CarbonInterface
                ? CarbonImmutable::instance($startsAt)
                : CarbonImmutable::parse((string) $startsAt),
            eventTypeLabel: (string) __('capell-campaign-studio::generic.editorial_calendar.event_types.' . str_replace('campaign.', '', $eventType)),
            status: (string) $campaign->status->getLabel(),
            recordUrl: $this->recordUrl($campaign),
            state: $campaign->status->value,
            siteId: is_numeric($campaign->site_id) ? (int) $campaign->site_id : null,
            siteName: $campaign->site?->name,
            timezone: config('app.timezone', 'UTC'),
            color: $eventType === self::END_EVENT_TYPE ? 'warning' : 'success',
            metadata: [
                'slug' => $campaign->slug,
                'utm_campaign' => $campaign->utm_campaign,
            ],
        );
    }

    private function recordUrl(CampaignGroup $campaign): ?string
    {
        if (! Route::has(CampaignGroupResource::getRouteBaseName() . '.edit')) {
            return null;
        }

        return CampaignGroupResource::getUrl('edit', ['record' => $campaign]);
    }
}
