<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class EditorialCalendarEventData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $id,
        public string $sourcePackage,
        public string $sourceType,
        public string $sourceId,
        public string $title,
        public string $eventType,
        public CarbonInterface $startsAt,
        public ?CarbonInterface $endsAt = null,
        public ?string $eventTypeLabel = null,
        public ?string $status = null,
        public ?string $description = null,
        public ?string $recordUrl = null,
        public ?string $state = null,
        public ?int $siteId = null,
        public ?string $siteName = null,
        public ?int $ownerId = null,
        public ?string $ownerName = null,
        public ?string $timezone = null,
        public bool $isAllDay = false,
        public ?string $color = null,
        public array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toCalendarRecord(): array
    {
        return [
            'id' => $this->id,
            'source_package' => $this->sourcePackage,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'title' => $this->title,
            'event_type' => $this->eventType,
            'event_type_label' => $this->eventTypeLabel,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'status' => $this->status,
            'description' => $this->description,
            'record_url' => $this->recordUrl,
            'state' => $this->state,
            'site_id' => $this->siteId,
            'site_name' => $this->siteName,
            'owner_id' => $this->ownerId,
            'owner_name' => $this->ownerName,
            'timezone' => $this->timezone,
            'is_all_day' => $this->isAllDay,
            'color' => $this->color,
            'metadata' => $this->metadata,
        ];
    }
}
