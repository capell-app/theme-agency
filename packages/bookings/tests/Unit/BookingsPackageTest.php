<?php

declare(strict_types=1);

use Capell\Bookings\Providers\BookingsServiceProvider;

require_once __DIR__ . '/../Pest.php';

use Capell\Bookings\Actions\BuildStaffCalendarFeedAction;
use Capell\Bookings\Actions\CancelAppointmentRequestAction;
use Capell\Bookings\Actions\ConfirmAppointmentRequestAction;
use Capell\Bookings\Actions\CreateAppointmentRequestAction;
use Capell\Bookings\Actions\CreateAvailabilityExceptionAction;
use Capell\Bookings\Actions\CreateStaffCalendarFeedUrlAction;
use Capell\Bookings\Actions\LinkBookingToPortalAccountAction;
use Capell\Bookings\Actions\MaterialiseLessonSeriesAction;
use Capell\Bookings\Actions\QueueAppointmentReminderAction;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Enums\BookingLocationTypeEnum;
use Capell\Bookings\Enums\ConfirmationPolicyEnum;
use Capell\Bookings\Health\BookingsHealthCheck;
use Capell\Bookings\Manifest\AppointmentRequestResourceContribution;
use Capell\Bookings\Manifest\BookingAvailabilityExceptionResourceContribution;
use Capell\Bookings\Manifest\BookingAvailabilityWindowResourceContribution;
use Capell\Bookings\Manifest\BookingLocationResourceContribution;
use Capell\Bookings\Manifest\BookingServiceResourceContribution;
use Capell\Bookings\Manifest\BookingsFrontendRoutesContribution;
use Capell\Bookings\Manifest\BookingsModelsContribution;
use Capell\Bookings\Manifest\BookingsReminderScheduleContribution;
use Capell\Bookings\Manifest\BookingStaffMemberResourceContribution;
use Capell\Bookings\Manifest\LessonSeriesResourceContribution;
use Capell\Bookings\Models\AppointmentAuditLog;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Capell\Bookings\Models\LessonSeries;
use Capell\Bookings\Settings\BookingsSettings;
use Capell\Bookings\Support\BookingsModelRegistrar;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Illuminate\Support\Facades\File;

it('keeps package manifest requirements aligned with composer requirements', function (): void {
    $manifest = File::json(__DIR__ . '/../../capell.json');
    $composer = File::json(__DIR__ . '/../../composer.json');

    $composerPackageRequirements = array_values(array_filter(
        array_keys($composer['require'] ?? []),
        fn (int|string $packageName): bool => is_string($packageName) && str_starts_with($packageName, 'capell-app/'),
    ));

    sort($composerPackageRequirements);

    $manifestRequirements = $manifest['dependencies']['requires'] ?? [];
    sort($manifestRequirements);

    expect($composerPackageRequirements)->toBe($manifestRequirements)
        ->and($manifest['database']['migrations'])->toBeTrue()
        ->and($manifest['database']['requiredTables'])->toBe([
            'booking_services',
            'booking_staff_members',
            'booking_locations',
            'booking_availability_windows',
            'booking_availability_exceptions',
            'lesson_series',
            'appointment_requests',
            'appointment_audit_logs',
        ])
        ->and($manifest['database']['settings'])->toBeTrue()
        ->and($manifest['settings'])->toBe([BookingsSettings::class])
        ->and($manifest['providers']['runtime'])->toContain(BookingsServiceProvider::class);
});

it('declares committed marketplace assets for every required screenshot capture target', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = File::json($packagePath . '/capell.json');
    $screenshotContract = File::json($packagePath . '/docs/screenshots.json');
    throw_unless(is_array($manifest), RuntimeException::class, 'Bookings manifest must decode to an array.');
    throw_unless(is_array($screenshotContract), RuntimeException::class, 'Bookings screenshot contract must decode to an array.');

    $marketplaceScreenshots = $manifest['marketplace']['screenshots'] ?? [];
    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($marketplaceScreenshots), RuntimeException::class, 'Bookings marketplace screenshots must be an array.');
    throw_unless(is_array($contractEntries), RuntimeException::class, 'Bookings screenshot contract entries must be an array.');

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshots as $marketplaceScreenshot) {
        throw_unless(is_array($marketplaceScreenshot), RuntimeException::class, 'Bookings marketplace screenshot entries must be arrays.');

        $path = $marketplaceScreenshot['path'] ?? null;
        $alt = $marketplaceScreenshot['alt'] ?? null;
        $caption = $marketplaceScreenshot['caption'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'Bookings marketplace screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'Bookings marketplace screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Bookings marketplace screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect(
            str_starts_with($path, 'docs/assets/marketplace/')
                || str_starts_with($path, 'docs/screenshots/'),
        )->toBeTrue()
            ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    $generatedFor = $screenshotContract['generatedFor'] ?? null;
    $composerRequires = $screenshotContract['composerRequires'] ?? [];
    throw_unless(is_array($composerRequires), RuntimeException::class, 'Bookings screenshot contract composer requirements must be an array.');

    expect($generatedFor)->toBe('deployment-screenshot-runner')
        ->and($composerRequires)->toContain('capell-app/bookings');

    $requiredMarketplaceScreenshotPaths = [];

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $id = $contractEntry['id'] ?? null;
        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($id), RuntimeException::class, 'Required Bookings screenshot contract entries must have string ids.');
        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required Bookings screenshot contract entries must have string screenshot paths.');

        $requiredMarketplaceScreenshotPaths[] = str_replace('packages/bookings/', '', $screenshotPath);
    }

    expect($marketplaceScreenshotPaths)
        ->toContain('docs/assets/marketplace/extension-card.svg')
        ->toContain(...$requiredMarketplaceScreenshotPaths);
});

it('declares implemented bookings contributions and feature capabilities', function (): void {
    $manifest = File::json(__DIR__ . '/../../capell.json');

    $contributions = collect($manifest['contributes']);

    expect($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
        && ($contribution['class'] ?? null) === BookingServiceResourceContribution::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === BookingStaffMemberResourceContribution::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === BookingLocationResourceContribution::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === BookingAvailabilityWindowResourceContribution::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === BookingAvailabilityExceptionResourceContribution::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === LessonSeriesResourceContribution::class))->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === AppointmentRequestResourceContribution::class))->toBeTrue()
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => BookingsModelsContribution::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'route',
            'class' => BookingsFrontendRoutesContribution::class,
        ])
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'scheduled-job'
            && ($contribution['class'] ?? null) === BookingsReminderScheduleContribution::class
            && ($contribution['command'] ?? null) === 'capell:bookings:send-due-reminders'))->toBeTrue()
        ->and(class_implements(BookingsReminderScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and(class_implements(BookingsFrontendRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and($manifest['capabilities'])->toContain(
            'bookings-availability',
            'bookings-appointment-requests',
            'bookings-client-identity',
            'bookings-flexible-confirmation-gate',
            'bookings-lesson-series',
            'bookings-provisional-holds',
            'bookings-settings',
            'bookings-notifications',
            'bookings-reminders',
            'bookings-calendar-feeds',
        )
        ->and($manifest['actions'])->toMatchArray([
            'createAvailabilityException' => CreateAvailabilityExceptionAction::class,
            'createAppointmentRequest' => CreateAppointmentRequestAction::class,
            'confirmAppointmentRequest' => ConfirmAppointmentRequestAction::class,
            'cancelAppointmentRequest' => CancelAppointmentRequestAction::class,
            'linkBookingToPortalAccount' => LinkBookingToPortalAccountAction::class,
            'materialiseLessonSeries' => MaterialiseLessonSeriesAction::class,
            'queueAppointmentReminder' => QueueAppointmentReminderAction::class,
            'createStaffCalendarFeedUrl' => CreateStaffCalendarFeedUrlAction::class,
            'buildStaffCalendarFeed' => BuildStaffCalendarFeedAction::class,
        ])
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([]);
});

it('checks bookings tables morph aliases and actions are discoverable', function (): void {
    BookingsModelRegistrar::register();

    $healthCheck = new BookingsHealthCheck;

    expect(BookingsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($healthCheck->missingTables())->toBe([])
        ->and($healthCheck->missingMorphAliases())->toBe([])
        ->and($healthCheck->unresolvableActions())->toBe([])
        ->and($healthCheck->passes())->toBeTrue();
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
    $availabilityException = BookingAvailabilityException::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => BookingAvailabilityStatusEnum::Blocked,
    ]);
    $appointmentRequest = AppointmentRequest::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
        'status' => AppointmentRequestStatusEnum::Requested,
        'confirmation_policy' => ConfirmationPolicyEnum::Manual,
    ]);
    $lessonSeries = LessonSeries::factory()->create([
        'service_id' => $service->getKey(),
        'staff_member_id' => $staffMember->getKey(),
        'location_id' => $location->getKey(),
    ]);

    expect($service->settings)->toBe(['intake' => true])
        ->and($service->availabilityWindows()->getRelated())->toBeInstanceOf(BookingAvailabilityWindow::class)
        ->and($service->availabilityExceptions()->getRelated())->toBeInstanceOf(BookingAvailabilityException::class)
        ->and($service->appointmentRequests()->getRelated())->toBeInstanceOf(AppointmentRequest::class)
        ->and($staffMember->settings)->toBe(['calendar' => 'primary'])
        ->and($staffMember->availabilityWindows()->getRelated())->toBeInstanceOf(BookingAvailabilityWindow::class)
        ->and($staffMember->availabilityExceptions()->getRelated())->toBeInstanceOf(BookingAvailabilityException::class)
        ->and($location->type)->toBe(BookingLocationTypeEnum::Virtual)
        ->and($location->availabilityExceptions()->getRelated())->toBeInstanceOf(BookingAvailabilityException::class)
        ->and($availabilityWindow->status)->toBe(BookingAvailabilityStatusEnum::Available)
        ->and($availabilityException->status)->toBe(BookingAvailabilityStatusEnum::Blocked)
        ->and($availabilityException->service?->is($service))->toBeTrue()
        ->and($availabilityException->staffMember?->is($staffMember))->toBeTrue()
        ->and($availabilityException->location?->is($location))->toBeTrue()
        ->and($availabilityWindow->service?->is($service))->toBeTrue()
        ->and($availabilityWindow->staffMember?->is($staffMember))->toBeTrue()
        ->and($availabilityWindow->location?->is($location))->toBeTrue()
        ->and($appointmentRequest->status)->toBe(AppointmentRequestStatusEnum::Requested)
        ->and($appointmentRequest->confirmation_policy)->toBe(ConfirmationPolicyEnum::Manual)
        ->and($appointmentRequest->status->blocksCapacity())->toBeTrue()
        ->and($appointmentRequest->service?->is($service))->toBeTrue()
        ->and($lessonSeries->service?->is($service))->toBeTrue()
        ->and($lessonSeries->appointmentRequests()->getRelated())->toBeInstanceOf(AppointmentRequest::class)
        ->and($appointmentRequest->auditLogs()->getRelated())->toBeInstanceOf(AppointmentAuditLog::class);
});
