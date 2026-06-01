<?php

declare(strict_types=1);

use Capell\Events\Actions\RegisterForEventOccurrenceAction;
use Capell\Events\Data\EventRegistrationData;
use Capell\Events\Enums\EventBookingModeEnum;
use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Events\EventRegistrationCreated;
use Capell\Events\Models\EventOccurrence;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('waitlists registrations once an occurrence reaches capacity', function (): void {
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'booking_mode' => EventBookingModeEnum::NativeRsvp,
        'capacity' => 1,
        'waitlist_enabled' => true,
    ]);

    $firstRegistration = RegisterForEventOccurrenceAction::run(
        $occurrence,
        new EventRegistrationData(name: 'Alice Example', email: 'alice@example.com'),
    );

    $secondRegistration = RegisterForEventOccurrenceAction::run(
        $occurrence->refresh(),
        new EventRegistrationData(name: 'Bob Example', email: 'bob@example.com'),
    );

    expect($firstRegistration->status)->toBe(EventRegistrationStatusEnum::Pending)
        ->and($secondRegistration->status)->toBe(EventRegistrationStatusEnum::Waitlisted)
        ->and($secondRegistration->waitlist_position)->toBe(1);
});

it('dispatches an event when a native event registration is created', function (): void {
    Event::fake([EventRegistrationCreated::class]);
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'booking_mode' => EventBookingModeEnum::NativeRsvp,
        'capacity' => 10,
    ]);

    $registration = RegisterForEventOccurrenceAction::run(
        $occurrence,
        new EventRegistrationData(name: 'Alice Example', email: 'alice@example.com'),
    );

    Event::assertDispatched(
        EventRegistrationCreated::class,
        fn (EventRegistrationCreated $event): bool => $event->registration->is($registration),
    );
});
