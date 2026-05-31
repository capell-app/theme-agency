<?php

declare(strict_types=1);

use Capell\Bookings\Providers\BookingsServiceProvider;

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Enums\BookingLocationTypeEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Illuminate\Support\Facades\File;

it('keeps package manifest requirements aligned with composer requirements', function (): void {
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
    );
    $composer = json_decode(
        File::get(__DIR__ . '/../../composer.json'),
        associative: true,
    );

    $composerPackageRequirements = array_values(array_filter(
        array_keys($composer['require'] ?? []),
        fn (int|string $packageName): bool => is_string($packageName) && str_starts_with($packageName, 'capell-app/'),
    ));

    sort($composerPackageRequirements);

    $manifestRequirements = $manifest['dependencies']['requires'] ?? [];
    sort($manifestRequirements);

    expect($composerPackageRequirements)->toBe($manifestRequirements)
        ->and($manifest['database']['migrations'])->toBeTrue()
        ->and($manifest['providers']['runtime'])->toContain(BookingsServiceProvider::class);
});

it('casts enums and exposes core relationships', function (): void {
    $service = BookingService::factory()->create(['settings' => ['intake' => true]]);
    $staffMember = BookingStaffMember::factory()->create(['settings' => ['calendar' => 'primary']]);
    $location = BookingLocation::factory()->create(['type' => BookingLocationTypeEnum::Virtual]);
    $availabilityWindow = BookingAvailabilityWindow::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => BookingAvailabilityStatusEnum::Available,
    ]);
    $appointmentRequest = AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Requested,
    ]);

    expect($service->settings)->toBe(['intake' => true])
        ->and($service->availabilityWindows()->getRelated())->toBeInstanceOf(BookingAvailabilityWindow::class)
        ->and($service->appointmentRequests()->getRelated())->toBeInstanceOf(AppointmentRequest::class)
        ->and($staffMember->settings)->toBe(['calendar' => 'primary'])
        ->and($staffMember->availabilityWindows()->getRelated())->toBeInstanceOf(BookingAvailabilityWindow::class)
        ->and($location->type)->toBe(BookingLocationTypeEnum::Virtual)
        ->and($availabilityWindow->status)->toBe(BookingAvailabilityStatusEnum::Available)
        ->and($availabilityWindow->service?->is($service))->toBeTrue()
        ->and($availabilityWindow->staffMember?->is($staffMember))->toBeTrue()
        ->and($availabilityWindow->location?->is($location))->toBeTrue()
        ->and($appointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Requested)
        ->and($appointmentRequest->status->blocksCapacity())->toBeTrue()
        ->and($appointmentRequest->service?->is($service))->toBeTrue();
});
