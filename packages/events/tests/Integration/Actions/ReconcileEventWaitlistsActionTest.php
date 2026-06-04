<?php

declare(strict_types=1);

use Capell\Events\Actions\ReconcileEventWaitlistsAction;
use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Illuminate\Support\Facades\Notification;

it('promotes stale waitlisted registrations when capacity is available', function (): void {
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'capacity' => 3,
        'registration_count' => 1,
        'waitlist_enabled' => true,
    ]);

    EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Confirmed,
        'quantity' => 1,
    ]);

    $firstWaitlistedRegistration = EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Waitlisted,
        'waitlist_position' => 1,
        'quantity' => 1,
    ]);
    $secondWaitlistedRegistration = EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Waitlisted,
        'waitlist_position' => 2,
        'quantity' => 1,
    ]);

    expect(ReconcileEventWaitlistsAction::run())->toBe(2)
        ->and($firstWaitlistedRegistration->refresh()->status)->toBe(EventRegistrationStatusEnum::Pending)
        ->and($secondWaitlistedRegistration->refresh()->status)->toBe(EventRegistrationStatusEnum::Pending)
        ->and($occurrence->refresh()->registration_count)->toBe(3);
});

it('leaves waitlisted registrations alone when no capacity is available', function (): void {
    Notification::fake();

    $occurrence = EventOccurrence::factory()->create([
        'capacity' => 1,
        'registration_count' => 1,
        'waitlist_enabled' => true,
    ]);

    EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Confirmed,
        'quantity' => 1,
    ]);

    $waitlistedRegistration = EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Waitlisted,
        'waitlist_position' => 1,
        'quantity' => 1,
    ]);

    expect(ReconcileEventWaitlistsAction::run())->toBe(0)
        ->and($waitlistedRegistration->refresh()->status)->toBe(EventRegistrationStatusEnum::Waitlisted)
        ->and($occurrence->refresh()->registration_count)->toBe(1);
});
