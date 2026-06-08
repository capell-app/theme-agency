<?php

declare(strict_types=1);

namespace Capell\Newsletter\Support\EditorialCalendar;

use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Filament\Resources\NewsletterSends\NewsletterSendResource;
use Capell\Newsletter\Models\NewsletterSend;
use Capell\Newsletter\Providers\NewsletterServiceProvider;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use RuntimeException;

final class NewsletterEditorialCalendarEventContributor implements EditorialCalendarEventContributor
{
    private const string EVENT_TYPE = 'newsletter.send';

    /**
     * @return Collection<int, EditorialCalendarEventData>
     */
    public function editorialCalendarEvents(EditorialCalendarQueryData $query): Collection
    {
        if (! $this->shouldContribute($query)) {
            return collect();
        }

        return NewsletterSend::query()
            ->with('site')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$query->startsAt, $query->endsAt])
            ->when($query->state !== null, fn (Builder $builder): Builder => $builder->where('status', $query->state))
            ->when($query->siteIds !== null, fn (Builder $builder): Builder => $builder->whereIn('site_id', $query->siteIds))
            ->oldest('scheduled_at')
            ->limit($query->limit)
            ->get()
            ->map(fn (NewsletterSend $send): EditorialCalendarEventData => $this->sendEvent($send));
    }

    private function shouldContribute(EditorialCalendarQueryData $query): bool
    {
        if ($query->sourceTypes !== null && ! in_array('newsletter', $query->sourceTypes, true)) {
            return false;
        }

        if ($query->eventTypes !== null && ! in_array(self::EVENT_TYPE, $query->eventTypes, true)) {
            return false;
        }

        return $query->ownerId === null && $query->ownerType === null;
    }

    private function sendEvent(NewsletterSend $send): EditorialCalendarEventData
    {
        $scheduledAt = $send->scheduled_at;

        throw_unless($scheduledAt instanceof CarbonInterface, RuntimeException::class, 'Newsletter editorial calendar sends must have a scheduled date.');

        return new EditorialCalendarEventData(
            id: 'newsletter-send-' . $send->id,
            sourcePackage: NewsletterServiceProvider::$packageName,
            sourceType: 'newsletter',
            sourceId: (string) $send->getKey(),
            title: $send->name,
            eventType: self::EVENT_TYPE,
            startsAt: $scheduledAt,
            eventTypeLabel: (string) __('capell-newsletter::generic.editorial_calendar.event_types.send'),
            status: $send->status->getLabel(),
            recordUrl: $this->recordUrl($send),
            state: $send->status->value,
            siteId: is_numeric($send->site_id) ? (int) $send->site_id : null,
            siteName: $send->site?->name,
            timezone: config('app.timezone', 'UTC'),
            color: $send->status === NewsletterSendStatus::Failed ? 'danger' : 'info',
            metadata: [
                'subject' => $send->subject,
                'segment_id' => $send->newsletter_segment_id,
                'provider_audience_id' => $send->newsletter_provider_audience_id,
                'utm_campaign' => $send->utm_campaign,
            ],
        );
    }

    private function recordUrl(NewsletterSend $send): ?string
    {
        if (! Route::has(NewsletterSendResource::getRouteBaseName() . '.edit')) {
            return null;
        }

        return NewsletterSendResource::getUrl('edit', ['record' => $send]);
    }
}
