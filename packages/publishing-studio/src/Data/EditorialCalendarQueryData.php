<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class EditorialCalendarQueryData extends Data
{
    /**
     * @param  list<string>|null  $sourceTypes
     * @param  list<string>|null  $eventTypes
     * @param  list<int>|null  $siteIds
     */
    public function __construct(
        public CarbonInterface $startsAt,
        public CarbonInterface $endsAt,
        public ?array $sourceTypes = null,
        public ?array $eventTypes = null,
        public ?array $siteIds = null,
        public ?int $ownerId = null,
        public ?string $ownerType = null,
        public ?string $state = null,
        public int $limit = 250,
    ) {}
}
