<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\EquestrianClinics\Console\Commands\ExpireSlotBookingHoldsCommand;
use Capell\EquestrianClinics\Console\Commands\ExpireWaitlistOffersCommand;
use Capell\EquestrianClinics\Health\EquestrianClinicsHealthCheck;
use Capell\EquestrianClinics\Manifest\EquestrianClinicsConsoleCommandsContribution;
use Capell\EquestrianClinics\Manifest\EquestrianClinicsExpireHoldsScheduleContribution;
use Capell\EquestrianClinics\Manifest\EquestrianClinicsExpireWaitlistOffersScheduleContribution;
use Capell\EquestrianClinics\Manifest\EquestrianClinicsHealthContribution;
use Capell\EquestrianClinics\Manifest\EquestrianClinicsModelsContribution;
use Capell\EquestrianClinics\Manifest\EquestrianClinicsRoutesContribution;
use Capell\EquestrianClinics\Models\EquestrianBillingEntry;
use Capell\EquestrianClinics\Models\EquestrianClinicCredit;
use Capell\EquestrianClinics\Models\EquestrianCommercialProduct;
use Capell\EquestrianClinics\Models\EquestrianCommunicationLog;
use Capell\EquestrianClinics\Models\EquestrianCompetitionResult;
use Capell\EquestrianClinics\Models\EquestrianFacilityBooking;
use Capell\EquestrianClinics\Models\EquestrianFacilityResource;
use Capell\EquestrianClinics\Models\EquestrianHorseCareTask;
use Capell\EquestrianClinics\Models\EquestrianHorseHealthRecord;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianHostRequest;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\EquestrianClinics\Models\EquestrianSlotWaitlistEntry;
use Capell\EquestrianClinics\Models\EquestrianStaffMember;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Capell\EquestrianClinics\Models\EquestrianVenue;
use Capell\EquestrianClinics\Models\EquestrianWaiverSignature;
use Capell\EquestrianClinics\Providers\EquestrianClinicsServiceProvider;
use Illuminate\Support\Facades\File;

it('keeps package manifest aligned with composer and the competitor feature matrix', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = loadPackageJsonArray($packagePath . '/capell.json');
    $composer = loadPackageJsonArray($packagePath . '/composer.json');

    $composerRequirements = array_values(array_filter(
        array_keys(arrayValue($composer, 'require')),
        static fn (int|string $packageName): bool => is_string($packageName) && str_starts_with($packageName, 'capell-app/'),
    ));
    sort($composerRequirements);

    $manifestRequirements = stringListValue(arrayValue($manifest, 'dependencies'), 'requires');
    sort($manifestRequirements);

    expect($composerRequirements)->toBe($manifestRequirements)
        ->and(stringListValue(arrayValue($manifest, 'providers'), 'runtime'))->toContain(EquestrianClinicsServiceProvider::class)
        ->and(stringListValue(arrayValue($manifest, 'database'), 'requiredTables'))->toContain(
            'equestrian_venues',
            'equestrian_tour_days',
            'equestrian_tour_day_slots',
            'equestrian_rider_profiles',
            'equestrian_horse_profiles',
            'equestrian_facility_resources',
            'equestrian_facility_bookings',
            'equestrian_waiver_signatures',
            'equestrian_clinic_credits',
            'equestrian_host_requests',
        )
        ->and(stringListValue(arrayValue($manifest, 'marketplace'), 'competitorCoverage'))->toContain(
            'tour-days-clinics',
            'public-discovery',
            'payments',
            'riders-families',
            'horses',
            'horse-allocation',
            'facilities-resources',
            'waivers-compliance',
            'add-ons',
            'credits-loyalty',
            'coach-dashboard',
            'coaching-vault',
            'messaging',
            'venue-host-workflow',
            'marketing',
            'analytics',
            'ai-assistant',
            'integrations',
        );
});

it('declares committed marketplace assets and a health check', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = loadPackageJsonArray($packagePath . '/capell.json');
    $screenshots = loadPackageJsonArray($packagePath . '/docs/screenshots.json');
    $marketplaceAsset = findScreenshotEntry($screenshots, 'equestrian-clinics-extension-card');
    $marketplaceScreenshotPaths = collect(listValue(arrayValue($manifest, 'marketplace'), 'screenshots'))
        ->map(static function (mixed $screenshot): string {
            if (! is_array($screenshot)) {
                throw new RuntimeException('Expected marketplace screenshot entry to be an object.');
            }

            return stringValue($screenshot, 'path');
        })
        ->all();

    expect(File::exists($packagePath . '/docs/assets/marketplace/extension-card.svg'))->toBeTrue()
        ->and(File::exists($packagePath . '/docs/screenshots/equestrian-clinics-public-discovery.png'))->toBeTrue()
        ->and(File::exists($packagePath . '/docs/screenshots/equestrian-clinics-coach-timetable.png'))->toBeTrue()
        ->and($marketplaceScreenshotPaths)->toBe([
            'docs/screenshots/equestrian-clinics-public-discovery.png',
            'docs/screenshots/equestrian-clinics-coach-timetable.png',
            'docs/assets/marketplace/extension-card.svg',
        ])
        ->and($marketplaceAsset['screenshotPath'])->toBe('packages/equestrian-clinics/docs/screenshots/equestrian-clinics-extension-card.svg')
        ->and((new EquestrianClinicsHealthCheck)->passes())->toBeTrue();
});

it('declares shipped route, model, and health contributions without claiming admin resources', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = loadPackageJsonArray($packagePath . '/capell.json');
    $contributions = listValue($manifest, 'contributes');
    $routeContribution = findContributionEntry($contributions, 'route');
    $modelContribution = findContributionEntry($contributions, 'model');
    $healthContribution = findContributionEntry($contributions, 'health-check');
    $scheduledJobs = collect(findContributionEntries($contributions, 'scheduled-job'))->keyBy('command');
    $consoleCommandContribution = findContributionEntry($contributions, 'console-command');

    expect(stringListValue($manifest, 'surfaces'))->toBe(['admin', 'frontend'])
        ->and(stringListValue($manifest, 'permissions'))->toBe([])
        ->and(stringListValue(arrayValue(arrayValue($manifest, 'security'), 'adminSurface'), 'permissions'))->toBe([])
        ->and(collect($contributions)->contains(
            static fn (mixed $contribution): bool => is_array($contribution) && ($contribution['type'] ?? null) === 'admin-resource',
        ))->toBeFalse()
        ->and(collect(listValue(arrayValue($manifest, 'contributionTraceability'), 'deferredContributions'))->pluck('type')->all())->toBe(['admin-resource']);

    expect($modelContribution)->toMatchArray([
        'type' => 'model',
        'class' => EquestrianClinicsModelsContribution::class,
        'modelClasses' => [
            EquestrianVenue::class,
            EquestrianTourDay::class,
            EquestrianTourDaySlot::class,
            EquestrianStaffMember::class,
            EquestrianRiderProfile::class,
            EquestrianHorseProfile::class,
            EquestrianSlotBooking::class,
            EquestrianSlotWaitlistEntry::class,
            EquestrianHorseCareTask::class,
            EquestrianHorseHealthRecord::class,
            EquestrianCompetitionResult::class,
            EquestrianFacilityResource::class,
            EquestrianFacilityBooking::class,
            EquestrianWaiverSignature::class,
            EquestrianClinicCredit::class,
            EquestrianCommercialProduct::class,
            EquestrianBillingEntry::class,
            EquestrianCommunicationLog::class,
            EquestrianHostRequest::class,
        ],
    ]);

    expect($routeContribution)->toMatchArray([
        'type' => 'route',
        'class' => EquestrianClinicsRoutesContribution::class,
        'routeNames' => [
            'capell-equestrian-clinics.discovery',
            'capell-equestrian-clinics.host-request.store',
            'capell-equestrian-clinics.coach.timetable',
        ],
        'public' => true,
        'signedRoutes' => ['capell-equestrian-clinics.coach.timetable'],
        'throttledRoutes' => ['capell-equestrian-clinics.host-request.store'],
    ]);

    expect($healthContribution)->toMatchArray([
        'type' => 'health-check',
        'class' => EquestrianClinicsHealthContribution::class,
        'checkClass' => EquestrianClinicsHealthCheck::class,
    ]);

    expect($scheduledJobs->get('capell:equestrian-clinics-expire-holds'))->toMatchArray([
        'type' => 'scheduled-job',
        'class' => EquestrianClinicsExpireHoldsScheduleContribution::class,
        'frequency' => 'everyFiveMinutes',
    ])
        ->and($scheduledJobs->get('capell:equestrian-clinics-expire-waitlist-offers'))->toMatchArray([
            'type' => 'scheduled-job',
            'class' => EquestrianClinicsExpireWaitlistOffersScheduleContribution::class,
            'frequency' => 'everyFiveMinutes',
        ]);

    expect($consoleCommandContribution)->toMatchArray([
        'type' => 'console-command',
        'class' => EquestrianClinicsConsoleCommandsContribution::class,
        'commands' => [
            'capell:equestrian-clinics-expire-holds',
            'capell:equestrian-clinics-expire-waitlist-offers',
        ],
        'commandClasses' => [
            ExpireSlotBookingHoldsCommand::class,
            ExpireWaitlistOffersCommand::class,
        ],
    ]);

    expect(class_implements(EquestrianClinicsModelsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(EquestrianClinicsRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(EquestrianClinicsHealthContribution::class))->toContain(ChecksExtensionHealth::class)
        ->and(class_implements(EquestrianClinicsConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(EquestrianClinicsExpireHoldsScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and(class_implements(EquestrianClinicsExpireWaitlistOffersScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class);
});

/**
 * @return array<string, mixed>
 */
function loadPackageJsonArray(string $path): array
{
    $payload = File::json($path);

    if (! is_array($payload)) {
        throw new RuntimeException('Expected package JSON to decode as an object.');
    }

    $result = [];

    foreach ($payload as $key => $value) {
        if (is_string($key)) {
            $result[$key] = $value;
        }
    }

    return $result;
}

/**
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function arrayValue(array $payload, string $key): array
{
    $value = $payload[$key] ?? null;

    if (! is_array($value)) {
        throw new RuntimeException('Expected JSON key "' . $key . '" to contain an object.');
    }

    $result = [];

    foreach ($value as $nestedKey => $nestedValue) {
        if (is_string($nestedKey)) {
            $result[$nestedKey] = $nestedValue;
        }
    }

    return $result;
}

/**
 * @param  array<string, mixed>  $payload
 * @return list<mixed>
 */
function listValue(array $payload, string $key): array
{
    $value = $payload[$key] ?? null;

    if (! is_array($value) || array_values($value) !== $value) {
        throw new RuntimeException('Expected JSON key "' . $key . '" to contain a list.');
    }

    return $value;
}

/**
 * @param  array<string, mixed>  $payload
 * @return list<string>
 */
function stringListValue(array $payload, string $key): array
{
    $values = listValue($payload, $key);
    $strings = [];

    foreach ($values as $value) {
        if (! is_string($value)) {
            throw new RuntimeException('Expected JSON key "' . $key . '" to contain only strings.');
        }

        $strings[] = $value;
    }

    return $strings;
}

/**
 * @param  list<mixed>  $entries
 * @return array<string, mixed>
 */
function arrayEntry(array $entries, int $index): array
{
    $entry = $entries[$index] ?? null;

    if (! is_array($entry)) {
        throw new RuntimeException('Expected JSON list entry to be an object.');
    }

    $result = [];

    foreach ($entry as $key => $value) {
        if (is_string($key)) {
            $result[$key] = $value;
        }
    }

    return $result;
}

/**
 * @param  array<string, mixed>  $payload
 */
function stringValue(array $payload, string $key): string
{
    $value = $payload[$key] ?? null;

    if (! is_string($value)) {
        throw new RuntimeException('Expected JSON key "' . $key . '" to contain a string.');
    }

    return $value;
}

/**
 * @param  array<string, mixed>  $screenshots
 * @return array<string, mixed>
 */
function findScreenshotEntry(array $screenshots, string $id): array
{
    foreach (listValue($screenshots, 'entries') as $entry) {
        if (is_array($entry) && ($entry['id'] ?? null) === $id) {
            return arrayEntry([$entry], 0);
        }
    }

    throw new RuntimeException('Screenshot entry was not found.');
}

/**
 * @param  list<mixed>  $contributions
 * @return array<string, mixed>
 */
function findContributionEntry(array $contributions, string $type): array
{
    foreach ($contributions as $contribution) {
        if (is_array($contribution) && ($contribution['type'] ?? null) === $type) {
            return arrayEntry([$contribution], 0);
        }
    }

    throw new RuntimeException('Manifest contribution entry was not found.');
}

/**
 * @param  list<mixed>  $contributions
 * @return list<array<string, mixed>>
 */
function findContributionEntries(array $contributions, string $type): array
{
    $matches = [];

    foreach ($contributions as $contribution) {
        if (is_array($contribution) && ($contribution['type'] ?? null) === $type) {
            $matches[] = arrayEntry([$contribution], 0);
        }
    }

    return $matches;
}
