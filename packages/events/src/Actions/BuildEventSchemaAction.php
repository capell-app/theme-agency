<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Enums\EventLocationModeEnum;
use Capell\Events\Enums\EventOccurrenceStatusEnum;
use Capell\Events\Models\EventOccurrence;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<string, mixed> run(EventOccurrence $occurrence)
 */
class BuildEventSchemaAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(EventOccurrence $occurrence): array
    {
        $event = $occurrence->event;
        $url = $occurrence->occurrenceUrl();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            '@id' => $url !== null ? $url . '#event' : null,
            'name' => $event->translation->title ?? $event->name,
            'description' => $event->translation?->meta_description,
            'startDate' => $occurrence->starts_at->toIso8601String(),
            'endDate' => $occurrence->ends_at?->toIso8601String(),
            'eventStatus' => $this->eventStatus($occurrence),
            'eventAttendanceMode' => $this->attendanceMode($occurrence->location_mode),
            'location' => $this->location($occurrence),
            'url' => $url,
            'offers' => $this->offers($occurrence),
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }

    private function eventStatus(EventOccurrence $occurrence): string
    {
        return match ($occurrence->status) {
            EventOccurrenceStatusEnum::Cancelled => 'https://schema.org/EventCancelled',
            EventOccurrenceStatusEnum::Postponed => 'https://schema.org/EventPostponed',
            default => 'https://schema.org/EventScheduled',
        };
    }

    private function attendanceMode(EventLocationModeEnum $locationMode): string
    {
        return match ($locationMode) {
            EventLocationModeEnum::Online => 'https://schema.org/OnlineEventAttendanceMode',
            EventLocationModeEnum::Hybrid => 'https://schema.org/MixedEventAttendanceMode',
            default => 'https://schema.org/OfflineEventAttendanceMode',
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function location(EventOccurrence $occurrence): ?array
    {
        if ($occurrence->location_mode === EventLocationModeEnum::Online) {
            return [
                '@type' => 'VirtualLocation',
                'url' => $occurrence->booking_url,
            ];
        }

        if ($occurrence->venue === null) {
            return null;
        }

        return [
            '@type' => 'Place',
            'name' => $occurrence->venue->name,
            'address' => $occurrence->venue->full_address,
            'hasMap' => $occurrence->venue->map_url,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function offers(EventOccurrence $occurrence): ?array
    {
        if ($occurrence->booking_url === null || $occurrence->booking_url === '') {
            return null;
        }

        return [
            '@type' => 'Offer',
            'url' => $occurrence->booking_url,
            'availability' => $occurrence->isFullForQuantity(1) ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
        ];
    }
}
