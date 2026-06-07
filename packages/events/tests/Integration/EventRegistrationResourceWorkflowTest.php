<?php

declare(strict_types=1);

use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Enums\EventOccurrenceStatusEnum;
use Capell\Events\Filament\Resources\Occurrences\Pages\ManageEventOccurrences;
use Capell\Events\Filament\Resources\Registrations\Pages\ManageEventRegistrations;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Carbon\CarbonImmutable;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(CreatesAdminUser::class);

it('lets staff confirm a pending registration from the registration resource', function (): void {
    test()->actingAsAdmin();

    $registration = EventRegistration::factory()->create([
        'status' => EventRegistrationStatusEnum::Pending,
    ]);

    Livewire::test(ManageEventRegistrations::class)
        ->callTableAction('confirm', $registration)
        ->assertHasNoTableActionErrors();

    expect($registration->refresh()->status)->toBe(EventRegistrationStatusEnum::Confirmed);
});

it('lets staff cancel a registration and promote the waitlist from the registration resource', function (): void {
    Notification::fake();
    test()->actingAsAdmin();

    $occurrence = EventOccurrence::factory()->create([
        'capacity' => 1,
        'registration_count' => 1,
    ]);

    $confirmedRegistration = EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Confirmed,
    ]);

    $waitlistedRegistration = EventRegistration::factory()->for($occurrence, 'occurrence')->create([
        'status' => EventRegistrationStatusEnum::Waitlisted,
        'waitlist_position' => 1,
    ]);

    Livewire::test(ManageEventRegistrations::class)
        ->callTableAction('cancel', $confirmedRegistration)
        ->assertHasNoTableActionErrors();

    expect($confirmedRegistration->refresh()->status)->toBe(EventRegistrationStatusEnum::Cancelled)
        ->and($confirmedRegistration->cancelled_at)->not->toBeNull()
        ->and($waitlistedRegistration->refresh()->status)->toBe(EventRegistrationStatusEnum::Pending)
        ->and($waitlistedRegistration->waitlist_position)->toBeNull()
        ->and($occurrence->refresh()->registration_count)->toBe(1);
});

it('lets staff cancel an occurrence from the occurrence resource', function (): void {
    test()->actingAsAdmin();

    $occurrence = EventOccurrence::factory()->create([
        'status' => EventOccurrenceStatusEnum::Scheduled,
    ]);

    Livewire::test(ManageEventOccurrences::class)
        ->callTableAction('cancelOccurrence', $occurrence, data: [
            'reason' => 'Speaker unavailable',
        ])
        ->assertHasNoTableActionErrors();

    $overrideData = $occurrence->refresh()->override_data;

    expect($occurrence->status)->toBe(EventOccurrenceStatusEnum::Cancelled)
        ->and($occurrence->is_override)->toBeTrue()
        ->and($overrideData['cancellation_reason'])->toBe('Speaker unavailable');
});

it('lets staff reschedule an occurrence from the occurrence resource', function (): void {
    test()->actingAsAdmin();

    $occurrence = EventOccurrence::factory()->create([
        'timezone' => 'UTC',
    ]);
    $startsAt = CarbonImmutable::parse('2026-06-18 14:00:00', 'UTC');
    $endsAt = $startsAt->addHours(2);

    Livewire::test(ManageEventOccurrences::class)
        ->callTableAction('rescheduleOccurrence', $occurrence, data: [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ])
        ->assertHasNoTableActionErrors();

    expect($occurrence->refresh()->starts_at->equalTo($startsAt))->toBeTrue()
        ->and($occurrence->ends_at?->equalTo($endsAt))->toBeTrue()
        ->and($occurrence->is_override)->toBeTrue();
});
