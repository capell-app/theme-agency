<?php

declare(strict_types=1);

use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Filament\Resources\Registrations\Pages\ManageEventRegistrations;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
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
