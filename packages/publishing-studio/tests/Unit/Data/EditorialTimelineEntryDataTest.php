<?php

declare(strict_types=1);

use Capell\PublishingStudio\Data\EditorialTimelineEntryData;
use Capell\PublishingStudio\Enums\EditorialTimelineEntryTypeEnum;
use Capell\PublishingStudio\Tests\PublishingStudioTestCase;
use Carbon\CarbonImmutable;

uses(PublishingStudioTestCase::class);

it('creates a timeline entry with type label color and metadata', function (): void {
    $occurredAt = CarbonImmutable::parse('2026-05-04 10:15:00');

    $entry = new EditorialTimelineEntryData(
        type: EditorialTimelineEntryTypeEnum::Approved,
        title: 'Approved by content lead',
        description: 'Ready for release.',
        occurredAt: $occurredAt,
        actorName: 'Ben Johnson',
        workspaceId: 12,
        versionId: null,
        entityType: 'page',
        entityId: 45,
        url: '/admin/workspaces/12/compare',
        metadata: ['level' => 2],
    );

    expect($entry->type->getLabel())->toBe('Approved')
        ->and($entry->type->getColor())->toBe('success')
        ->and($entry->occurredAt)->toBe($occurredAt)
        ->and($entry->metadata)->toBe(['level' => 2]);
});
