<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\InstallBookingsDemoAction;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('installs idempotent bookings demo fixtures', function (): void {
    DB::table('sites')->insert(['id' => 10]);

    $firstRun = InstallBookingsDemoAction::run();
    $secondRun = InstallBookingsDemoAction::run();

    expect($firstRun)->toBe([
        'services' => 1,
        'staff' => 1,
        'locations' => 1,
        'availability_windows' => 1,
        'appointment_requests' => 1,
    ])->and($secondRun)->toBe($firstRun)
        ->and(BookingService::query()->where('name', 'Demo consultation')->count())->toBe(1)
        ->and(BookingStaffMember::query()->where('display_name', 'Avery Morgan')->count())->toBe(1)
        ->and(BookingLocation::query()->where('name', 'Demo studio')->count())->toBe(1)
        ->and(BookingAvailabilityWindow::query()->count())->toBe(1)
        ->and(AppointmentRequest::query()->where('source', 'bookings-demo')->first()?->site_id)->toBe(10);
});

it('registers and runs the bookings demo command', function (): void {
    expect(Artisan::all())->toHaveKey('capell:bookings-demo');

    $this->artisan('capell:bookings-demo')
        ->assertSuccessful();

    expect(BookingService::query()->where('name', 'Demo consultation')->exists())->toBeTrue()
        ->and(AppointmentRequest::query()->where('source', 'bookings-demo')->exists())->toBeTrue();
});
