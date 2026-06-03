<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Events\Health\EventsHealthCheck;
use Illuminate\Support\Facades\Schema;

it('reports a compatible capell api version', function (): void {
    expect(EventsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = EventsHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(4)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when tables, libraries, and morph aliases are present', function (): void {
    $results = EventsHealthCheck::runDiagnostics();

    expect(EventsHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the recurrence check when the occurrence table is missing', function (): void {
    Schema::drop('event_occurrences');

    $check = new EventsHealthCheck;

    expect($check->recurrenceCheck()->passed)->toBeFalse()
        ->and(EventsHealthCheck::passed())->toBeFalse();
});

it('fails the registration capacity check when the registrations table is missing', function (): void {
    Schema::drop('event_registrations');

    $check = new EventsHealthCheck;

    expect($check->registrationCapacityCheck()->passed)->toBeFalse()
        ->and(EventsHealthCheck::passed())->toBeFalse();
});

it('confirms the event models are registered in the morph map', function (): void {
    $check = new EventsHealthCheck;

    expect($check->unregisteredMorphAliases())->toBe([])
        ->and($check->registrationCapacityCheck()->passed)->toBeTrue();
});
