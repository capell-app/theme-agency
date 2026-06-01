<?php

declare(strict_types=1);

use Capell\CustomerPortal\Actions\ResolvePortalSelfServiceItemsAction;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Carbon\CarbonImmutable;

it('registers event registration self service items for customer portal accounts', function (): void {
    $occurrence = EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-07-01 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-07-01 11:00:00', 'UTC'),
    ]);
    $site = $occurrence->event->site;
    $registration = EventRegistration::factory()->create([
        'event_occurrence_id' => $occurrence->getKey(),
        'email' => 'Attendee@Example.com',
        'status' => EventRegistrationStatusEnum::Confirmed,
        'quantity' => 2,
        'registered_at' => CarbonImmutable::parse('2026-06-01 09:00:00', 'UTC'),
    ]);
    $otherOccurrence = EventOccurrence::factory()->create();
    EventRegistration::factory()->create([
        'event_occurrence_id' => $otherOccurrence->getKey(),
        'email' => 'other-attendee@example.com',
    ]);
    $portalAccount = PortalAccount::query()->create([
        'site_id' => $site->getKey(),
        'email' => 'attendee@example.com',
        'display_name' => 'Attendee Example',
    ]);

    $items = ResolvePortalSelfServiceItemsAction::run($portalAccount);
    $item = collect($items)->first();

    expect($items)->toHaveCount(1)
        ->and($item)->toBeInstanceOf(PortalSelfServiceItemData::class);

    throw_unless($item instanceof PortalSelfServiceItemData, RuntimeException::class, 'Expected portal item.');

    expect($item->key)->toBe('events.registration.' . $registration->getKey())
        ->and($item->type)->toBe(PortalSelfServiceItemType::EventRegistration)
        ->and($item->label)->toBe($occurrence->event->name)
        ->and($item->description)->toContain('2 registered')
        ->and($item->status)->toBe(EventRegistrationStatusEnum::Confirmed->getLabel())
        ->and($item->meta)->toBe([
            'registration_id' => (int) $registration->getKey(),
            'occurrence_id' => (int) $occurrence->getKey(),
            'status' => EventRegistrationStatusEnum::Confirmed->value,
        ]);
});

it('declares customer portal registration feed metadata in the events manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['dependencies']['supports'] ?? [])->toContain('capell-app/customer-portal')
        ->and($manifest['capabilities'])->toContain('events-customer-portal-registration-feed')
        ->and($manifest['database']['requiredTables'])->toContain(
            'event_venues',
            'events',
            'event_occurrences',
            'event_registrations',
            'event_notification_logs',
        );
});
