<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Data;

use Capell\PublishingStudio\Enums\EditorialTimelineEntryTypeEnum;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class EditorialTimelineEntryData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly EditorialTimelineEntryTypeEnum $type,
        public readonly string $title,
        public readonly ?string $description,
        public readonly CarbonImmutable $occurredAt,
        public readonly ?string $actorName = null,
        public readonly ?int $workspaceId = null,
        public readonly ?int $versionId = null,
        public readonly ?string $entityType = null,
        public readonly int|string|null $entityId = null,
        public readonly ?string $url = null,
        public readonly array $metadata = [],
    ) {}
}
