<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;
use Capell\ThemeStudio\InertiaBookingsReact\Health\InertiaBookingsReactHealthCheck;
use Capell\ThemeStudio\InertiaBookingsReact\Manifest\InertiaBookingsReactComponentContribution;
use Capell\ThemeStudio\InertiaBookingsReact\Providers\InertiaBookingsReactServiceProvider;

require_once __DIR__ . '/../../src/Health/InertiaBookingsReactHealthCheck.php';
require_once __DIR__ . '/../../src/Manifest/InertiaBookingsReactComponentContribution.php';
require_once __DIR__ . '/../../src/Providers/InertiaBookingsReactServiceProvider.php';

it('keeps the package manifest aligned with the react component pack', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['name'])->toBe(InertiaBookingsReactServiceProvider::$packageName)
        ->and($manifest['kind'])->toBe('plugin')
        ->and($manifest['surfaces'])->toBe(['frontend'])
        ->and($manifest['dependencies']['requires'])->toBe([
            'capell-app/theme-inertia-bookings',
            'capell-app/inertia-react-adapter',
        ])
        ->and($manifest['providers']['runtime'])->toBe([
            InertiaBookingsReactServiceProvider::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'frontend-component',
            'class' => InertiaBookingsReactComponentContribution::class,
            'component' => 'Capell/Bookings/Request',
            'adapter' => 'react',
            'surface' => 'frontend',
        ])
        ->and(class_implements(InertiaBookingsReactComponentContribution::class))->toContain(RegistersExtensionFrontendComponent::class)
        ->and(InertiaBookingsReactComponentContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($manifest['healthChecks'][0]['class'])->toBe(InertiaBookingsReactHealthCheck::class)
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

        throw_unless(is_string($path), RuntimeException::class, 'React component pack screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'React component pack screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'React component pack screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect(str_starts_with($path, 'docs/assets/marketplace/') || str_starts_with($path, 'docs/screenshots/'))->toBeTrue()
            ->and(file_exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($contractEntries), RuntimeException::class, 'React component pack screenshot contract entries must be an array.');

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required React component pack screenshot entries must have screenshot paths.');

        expect($marketplaceScreenshotPaths)->toContain(str_replace('packages/theme-inertia-bookings-react/', '', $screenshotPath));
    }

    expect($marketplaceScreenshotPaths)->toContain('docs/assets/marketplace/extension-card.svg');
});

it('keeps the react booking request form explicitly labelled', function (): void {
    $component = (string) file_get_contents(__DIR__ . '/../../resources/js/Pages/Capell/Bookings/Request.jsx');

    expect(substr_count($component, '<label>'))->toBeGreaterThanOrEqual(8)
        ->and($component)->not->toContain('placeholder=');
});
