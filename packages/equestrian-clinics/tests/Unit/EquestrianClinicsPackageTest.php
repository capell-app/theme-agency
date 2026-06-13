<?php

declare(strict_types=1);

use Capell\EquestrianClinics\Health\EquestrianClinicsHealthCheck;
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
        ->and(stringListValue($manifest, 'competitorCoverage'))->toContain(
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
    $marketplaceScreenshot = arrayEntry(listValue(arrayValue($manifest, 'marketplace'), 'screenshots'), 0);

    expect(File::exists($packagePath . '/docs/assets/marketplace/extension-card.svg'))->toBeTrue()
        ->and(stringValue($marketplaceScreenshot, 'path'))->toBe('docs/assets/marketplace/extension-card.svg')
        ->and($marketplaceAsset['screenshotPath'])->toBe('packages/equestrian-clinics/docs/screenshots/equestrian-clinics-extension-card.svg')
        ->and((new EquestrianClinicsHealthCheck)->passes())->toBeTrue();
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
