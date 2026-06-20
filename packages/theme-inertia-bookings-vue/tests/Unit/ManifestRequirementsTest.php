<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;
use Capell\ThemeStudio\InertiaBookingsVue\Health\InertiaBookingsVueHealthCheck;
use Capell\ThemeStudio\InertiaBookingsVue\Manifest\InertiaBookingsVueComponentContribution;
use Capell\ThemeStudio\InertiaBookingsVue\Providers\InertiaBookingsVueServiceProvider;

require_once __DIR__ . '/../../src/Health/InertiaBookingsVueHealthCheck.php';
require_once __DIR__ . '/../../src/Manifest/InertiaBookingsVueComponentContribution.php';
require_once __DIR__ . '/../../src/Providers/InertiaBookingsVueServiceProvider.php';

it('keeps the package manifest aligned with the vue component pack', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['name'])->toBe(InertiaBookingsVueServiceProvider::$packageName)
        ->and($manifest['kind'])->toBe('plugin')
        ->and($manifest['surfaces'])->toBe(['frontend'])
        ->and($manifest['dependencies']['requires'])->toBe([
            'capell-app/theme-inertia-bookings',
            'capell-app/inertia-vue-adapter',
        ])
        ->and($manifest['providers']['runtime'])->toBe([
            InertiaBookingsVueServiceProvider::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'frontend-component',
            'class' => InertiaBookingsVueComponentContribution::class,
            'component' => 'Capell/Bookings/Request',
            'adapter' => 'vue',
            'surface' => 'frontend',
        ])
        ->and(class_implements(InertiaBookingsVueComponentContribution::class))->toContain(RegistersExtensionFrontendComponent::class)
        ->and(InertiaBookingsVueComponentContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($manifest['healthChecks'][0]['class'])->toBe(InertiaBookingsVueHealthCheck::class)
        ->and($manifest['healthChecks'][0]['surface'])->toBe('frontend')
        ->and($manifest['marketplace']['screenshots'])->not->toBeEmpty();

    $packagePath = dirname(__DIR__, 2);
    $screenshotContract = json_decode(
        (string) file_get_contents($packagePath . '/docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $marketplaceScreenshotPaths = [];

    foreach ($manifest['marketplace']['screenshots'] as $marketplaceScreenshot) {
        $path = $marketplaceScreenshot['path'] ?? null;
        $alt = $marketplaceScreenshot['alt'] ?? null;
        $caption = $marketplaceScreenshot['caption'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'Vue component pack screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'Vue component pack screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Vue component pack screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect(str_starts_with($path, 'docs/assets/marketplace/') || str_starts_with($path, 'docs/screenshots/'))->toBeTrue()
            ->and(file_exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($contractEntries), RuntimeException::class, 'Vue component pack screenshot contract entries must be an array.');

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required Vue component pack screenshot entries must have screenshot paths.');

        expect(file_exists(dirname(__DIR__, 4) . '/' . $screenshotPath))->toBeTrue()
            ->and($marketplaceScreenshotPaths)->toContain(str_replace('packages/theme-inertia-bookings-vue/', '', $screenshotPath));
    }

    expect($marketplaceScreenshotPaths)->toBe([
        'docs/assets/marketplace/extension-card.svg',
        'docs/screenshots/vue-booking-components.png',
        'docs/screenshots/vue-booking-services.png',
        'docs/screenshots/vue-booking-slot-loading.png',
        'docs/screenshots/vue-booking-validation.png',
        'docs/screenshots/vue-booking-mobile.png',
    ]);
});

it('keeps vue component map entries aligned with source files and manifest declarations', function (): void {
    $basePath = dirname(__DIR__, 2);
    $manifest = json_decode(
        (string) file_get_contents($basePath . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $entrypoint = (string) file_get_contents($basePath . '/resources/js/app.js');

    $contributes = is_array($manifest) && isset($manifest['contributes']) && is_array($manifest['contributes'])
        ? $manifest['contributes']
        : [];

    $declaredComponents = collect($contributes)
        ->filter(static fn (mixed $contribution): bool => is_array($contribution) && ($contribution['type'] ?? null) === 'frontend-component')
        ->map(static fn (array $contribution): string => (string) $contribution['component'])
        ->values();

    expect($declaredComponents->all())->toBe(['Capell/Bookings/Request'])
        ->and($entrypoint)->toContain("import Page from './Pages/Capell/Page.vue'")
        ->and($entrypoint)->toContain("import BookingRequest from './Pages/Capell/Bookings/Request.vue'")
        ->and($entrypoint)->toContain("'Capell/Page': Page")
        ->and($entrypoint)->toContain("'Capell/Bookings/Request': BookingRequest")
        ->and($entrypoint)->toContain('pages[name] ?? Page')
        ->and(file_exists($basePath . '/resources/js/Pages/Capell/Page.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Pages/Capell/Bookings/Request.vue'))->toBeTrue();

    foreach ($declaredComponents as $component) {
        $componentFile = $basePath . '/resources/js/Pages/' . $component . '.vue';

        expect($entrypoint)->toContain("'" . $component . "':")
            ->and(file_exists($componentFile))->toBeTrue();
    }
});
