<?php

declare(strict_types=1);

use Capell\Core\Enums\BlueprintSubjectEnum;
use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Layout;
use Capell\Events\Actions\CancelOccurrenceAction;
use Capell\Events\Actions\EnsureEventPublishingDefaultsAction;
use Capell\Events\Actions\EnsureEventPublishingSurfaceAction;
use Capell\Events\Actions\InstallPackageAction;
use Capell\Events\Actions\RescheduleOccurrenceAction;
use Capell\Events\Enums\EventOccurrenceStatusEnum;
use Capell\Events\Enums\LivewireComponentEnum;
use Capell\Events\Models\EventOccurrence;
use Carbon\CarbonImmutable;

it('cancels event occurrences with override metadata', function (): void {
    $occurrence = EventOccurrence::factory()->create([
        'override_data' => ['source' => 'manual'],
    ]);

    $cancelled = CancelOccurrenceAction::run($occurrence, 'Speaker unavailable');
    $cancelledOverrideData = $cancelled->override_data;

    expect($cancelled->status)->toBe(EventOccurrenceStatusEnum::Cancelled)
        ->and($cancelled->is_override)->toBeTrue()
        ->and($cancelledOverrideData['source'])->toBe('manual')
        ->and($cancelledOverrideData['cancellation_reason'])->toBe('Speaker unavailable')
        ->and($cancelledOverrideData['cancelled_at'])->not->toBeNull();
});

it('reschedules event occurrences with override metadata', function (): void {
    $occurrence = EventOccurrence::factory()->create([
        'override_data' => ['source' => 'calendar'],
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-01 14:00:00', 'UTC');
    $endsAt = $startsAt->addHours(3);

    $rescheduled = RescheduleOccurrenceAction::run($occurrence, $startsAt, $endsAt);
    $rescheduledOverrideData = $rescheduled->override_data;

    expect($rescheduled->starts_at->equalTo($startsAt))->toBeTrue()
        ->and($rescheduled->ends_at->equalTo($endsAt))->toBeTrue()
        ->and($rescheduled->is_override)->toBeTrue()
        ->and($rescheduledOverrideData['source'])->toBe('calendar')
        ->and($rescheduledOverrideData['rescheduled_at'])->not->toBeNull();
});

it('ensures event publishing defaults for event pages and listing pages', function (): void {
    EnsureEventPublishingDefaultsAction::run();

    $eventType = Blueprint::query()->where([
        'key' => 'event',
        'type' => BlueprintSubjectEnum::Page,
    ])->firstOrFail();
    $listingType = Blueprint::query()->where([
        'key' => 'events',
        'type' => BlueprintSubjectEnum::Page,
    ])->firstOrFail();

    expect($eventType->meta['schema']['type'])->toBe('Event')
        ->and($eventType->meta['with_date'])->toBeTrue()
        ->and($listingType->component)->toBe(LivewireComponentEnum::EventsCalendarPage->value)
        ->and(Layout::query()->where('key', 'event')->exists())->toBeTrue()
        ->and(Layout::query()->where('key', LayoutEnum::Results->value)->exists())->toBeTrue();
});

it('ensures an events listing publishing page with translations and urls', function (): void {
    $occurrence = EventOccurrence::factory()->create();
    $site = $occurrence->event->site;

    $page = EnsureEventPublishingSurfaceAction::run($site);

    expect($page->site_id)->toBe($site->getKey())
        ->and($page->name)->toBe(__('capell-events::generic.events'))
        ->and($page->translations()->where('title', __('capell-events::generic.events'))->exists())->toBeTrue()
        ->and($page->pageUrls()->exists())->toBeTrue();
});

it('installs event publishing defaults and surfaces for existing sites', function (): void {
    $occurrence = EventOccurrence::factory()->create();
    $site = $occurrence->event->site;

    InstallPackageAction::run();

    expect(Blueprint::query()->where('key', 'event')->exists())->toBeTrue()
        ->and(Blueprint::query()->where('key', 'events')->exists())->toBeTrue()
        ->and($site->pages()->where('name', __('capell-events::generic.events'))->exists())->toBeTrue();
});
