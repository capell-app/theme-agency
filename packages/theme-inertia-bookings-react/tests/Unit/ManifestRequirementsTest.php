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

        expect(file_exists(dirname(__DIR__, 4) . '/' . $screenshotPath))->toBeTrue()
            ->and($marketplaceScreenshotPaths)->toContain(str_replace('packages/theme-inertia-bookings-react/', '', $screenshotPath));
    }

    expect($marketplaceScreenshotPaths)->toBe([
        'docs/assets/marketplace/extension-card.svg',
        'docs/screenshots/react-booking-components.png',
        'docs/screenshots/react-booking-services.png',
        'docs/screenshots/react-booking-slot-loading.png',
        'docs/screenshots/react-booking-validation.png',
        'docs/screenshots/react-booking-mobile.png',
    ]);
});

it('keeps the react booking request form explicitly labelled', function (): void {
    $component = (string) file_get_contents(__DIR__ . '/../../resources/js/Pages/Capell/Bookings/Request.jsx');

    expect(substr_count($component, '<label>'))->toBeGreaterThanOrEqual(8)
        ->and($component)->not->toContain('placeholder=');
});

it('keeps react component map entries aligned with source files and manifest declarations', function (): void {
    $basePath = dirname(__DIR__, 2);
    $manifest = json_decode(
        (string) file_get_contents($basePath . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $entrypoint = (string) file_get_contents($basePath . '/resources/js/app.jsx');

    $declaredComponents = collect($manifest['contributes'] ?? [])
        ->filter(static fn (mixed $contribution): bool => is_array($contribution) && ($contribution['type'] ?? null) === 'frontend-component')
        ->map(static fn (array $contribution): string => (string) $contribution['component'])
        ->values();

    expect($declaredComponents->all())->toBe(['Capell/Bookings/Request'])
        ->and($entrypoint)->toContain("import Page from './Pages/Capell/Page.jsx'")
        ->and($entrypoint)->toContain("import BookingRequest from './Pages/Capell/Bookings/Request.jsx'")
        ->and($entrypoint)->toContain("'Capell/Page': Page")
        ->and($entrypoint)->toContain("'Capell/Bookings/Request': BookingRequest")
        ->and($entrypoint)->toContain('pages[name] ?? Page')
        ->and(file_exists($basePath . '/resources/js/Pages/Capell/Page.jsx'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Pages/Capell/Bookings/Request.jsx'))->toBeTrue();

    foreach ($declaredComponents as $component) {
        $componentFile = $basePath . '/resources/js/Pages/' . $component . '.jsx';

        expect($entrypoint)->toContain("'" . $component . "':")
            ->and(file_exists($componentFile))->toBeTrue();
    }
});

it('keeps the react booking request validation and loading states screenshotable', function (): void {
    $component = (string) file_get_contents(__DIR__ . '/../../resources/js/Pages/Capell/Bookings/Request.jsx');

    expect(substr_count($component, 'export default function Request'))->toBe(1)
        ->and($component)->toContain('errors, clearErrors')
        ->and($component)->toContain('className="error"')
        ->and($component)->toContain('role="alert"')
        ->and($component)->toContain("aria-describedby={describedBy('service_id')}")
        ->and($component)->toContain('requested_starts_at')
        ->and($component)->toContain("aria-invalid={errors?.service_id ? 'true' : undefined}")
        ->and($component)->toContain('className="slots"')
        ->and($component)->toContain('aria-live="polite"')
        ->and($component)->toContain('aria-busy="true"')
        ->and($component)->toContain('Loading available times')
        ->and($component)->toContain('clearErrors(field)')
        ->and($component)->not->toContain('                            </form>');
});

it('keeps react raw html rendering behind the public html sanitizer', function (): void {
    $basePath = dirname(__DIR__, 2);
    $pageComponent = (string) file_get_contents($basePath . '/resources/js/Pages/Capell/Page.jsx');
    $contentWidget = (string) file_get_contents($basePath . '/resources/js/Components/Capell/Widgets/Content.jsx');
    $sanitizer = (string) file_get_contents($basePath . '/resources/js/Support/publicHtml.js');

    expect($pageComponent)->toContain('sanitizePublicHtml(page.content)')
        ->and($contentWidget)->toContain('sanitizePublicHtml(widget.data.content)')
        ->and(substr_count($pageComponent . $contentWidget, 'dangerouslySetInnerHTML'))->toBe(2)
        ->and($sanitizer)->toContain('data-capell-authoring')
        ->and($sanitizer)->toContain('signed[-_]editor')
        ->and($sanitizer)->toContain('field_path')
        ->and($sanitizer)->toContain('model_id')
        ->and($sanitizer)->toContain('permission')
        ->and($sanitizer)->toContain('capell-app\\/[a-z0-9-]+')
        ->and($sanitizer)->toContain('javascript:')
        ->and($sanitizer)->toContain('signature')
        ->and($sanitizer)->toContain('admin');
});
