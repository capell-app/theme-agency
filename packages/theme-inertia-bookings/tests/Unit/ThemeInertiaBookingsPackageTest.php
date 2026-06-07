<?php

declare(strict_types=1);

use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Manifest\ManifestValidator;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Marketplace\Filament\Pages\ThemeExtensionPage;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\InertiaBookings\Health\ThemeInertiaBookingsHealthCheck;
use Capell\ThemeStudio\InertiaBookings\Manifest\ThemeManagementPageContribution;
use Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaBookingsThemeRenderer;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaPublicBookingRequestRenderer;
use Composer\Autoload\ClassLoader;
use Illuminate\Support\Facades\File;

$composerAutoloader = require dirname(__DIR__, 4) . '/vendor/autoload.php';

if ($composerAutoloader instanceof ClassLoader) {
    $composerAutoloader->addPsr4('Capell\\ThemeStudio\\InertiaBookings\\', dirname(__DIR__, 2) . '/src');
}

uses(PackagesTestCase::class);

it('declares valid Capell extension manifest metadata', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), associative: true, flags: JSON_THROW_ON_ERROR);
    $composer = json_decode(File::get($packagePath . '/composer.json'), associative: true, flags: JSON_THROW_ON_ERROR);

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Inertia Bookings manifest must decode to an array.');
    throw_unless(is_array($composer), RuntimeException::class, 'Theme Inertia Bookings composer metadata must decode to an array.');

    (new ManifestValidator)->validate($manifest, $composer, 'capell-app/theme-inertia-bookings', $packagePath . '/capell.json');

    expect($manifest['contributes'])->toContain([
        'type' => 'admin-page',
        'class' => ThemeManagementPageContribution::class,
        'pageClass' => ThemeExtensionPage::class,
        'pageParameters' => [
            'themeKey' => InertiaBookingsThemeServiceProvider::THEME_KEY,
        ],
        'labelKey' => 'capell-marketplace::marketplace.theme_extension.management_entry_label',
    ])
        ->and(class_implements(ThemeManagementPageContribution::class))->toContain(ExtensionContribution::class)
        ->and(ThemeManagementPageContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(ThemeManagementPageContribution::themeKey())->toBe(InertiaBookingsThemeServiceProvider::THEME_KEY)
        ->and($manifest['product']['tier'])->toBe('premium')
        ->and($manifest['healthChecks'][0]['class'])->toBe(ThemeInertiaBookingsHealthCheck::class)
        ->and(ThemeInertiaBookingsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('defines the inertia bookings theme contract', function (): void {
    $definition = InertiaBookingsThemeServiceProvider::definition();

    expect($definition->package)->toBe(InertiaBookingsThemeServiceProvider::$packageName)
        ->and($definition->key)->toBe(InertiaBookingsThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Inertia Bookings')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/inertia-bookings.css'])
        ->and($definition->includedSections)->toBe([
            'navigation',
            'hero',
            'services',
            'proof',
            'booking',
            'locations',
            'faq',
            'cta',
            'footer',
        ])
        ->and($definition->runtime)->toBe(FrontendRuntime::Inertia);
});

it('keeps marketplace media separate from unverified screenshot capture targets', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);
    $screenshotContract = json_decode(File::get($packagePath . '/docs/screenshots.json'), true, flags: JSON_THROW_ON_ERROR);

    $marketplaceScreenshots = $manifest['marketplace']['screenshots'] ?? [];
    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($marketplaceScreenshots), RuntimeException::class, 'Theme Inertia Bookings marketplace screenshots must be an array.');
    throw_unless(is_array($contractEntries), RuntimeException::class, 'Theme Inertia Bookings screenshot contract entries must be an array.');

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshots as $marketplaceScreenshot) {
        throw_unless(is_array($marketplaceScreenshot), RuntimeException::class, 'Theme Inertia Bookings marketplace screenshot entries must be arrays.');

        $path = $marketplaceScreenshot['path'] ?? null;
        $alt = $marketplaceScreenshot['alt'] ?? null;
        $caption = $marketplaceScreenshot['caption'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'Theme Inertia Bookings marketplace screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'Theme Inertia Bookings marketplace screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Theme Inertia Bookings marketplace screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect(str_starts_with($path, 'docs/assets/marketplace/') || str_starts_with($path, 'docs/screenshots/'))->toBeTrue()
            ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required Theme Inertia Bookings screenshot contract entries must have screenshot paths.');

        expect(File::exists(dirname(__DIR__, 4) . '/' . $screenshotPath))->toBeTrue()
            ->and($marketplaceScreenshotPaths)->not->toContain(str_replace('packages/theme-inertia-bookings/', '', $screenshotPath));
    }

    expect($marketplaceScreenshotPaths)
        ->toContain('docs/assets/marketplace/extension-card.svg');
});

it('registers the theme, booking renderer, and inertia theme assets when installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(InertiaBookingsThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $this->app->instance(ThemeRegistry::class, $registry);

    $provider = new InertiaBookingsThemeServiceProvider($this->app);
    $provider->register();
    $provider->packageBooted();

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(static fn (mixed $asset): bool => $asset->packageName === InertiaBookingsThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(static fn (mixed $asset): bool => $asset->packageName === InertiaBookingsThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($registry->has(InertiaBookingsThemeServiceProvider::THEME_KEY))->toBeTrue()
        ->and($registry->definition(InertiaBookingsThemeServiceProvider::THEME_KEY)->runtime)->toBe(FrontendRuntime::Inertia)
        ->and($registry->renderer(InertiaBookingsThemeServiceProvider::THEME_KEY))->toBeInstanceOf(InertiaBookingsThemeRenderer::class)
        ->and($this->app->make(PublicBookingRequestRenderer::class))->toBeInstanceOf(InertiaPublicBookingRequestRenderer::class)
        ->and($packageImports)->toContain('resources/css/theme-inertia-bookings.css')
        ->and($packageSources)->toContain('resources/js/**/*.vue', 'resources/js/**/*.jsx')
        ->and((new ThemeInertiaBookingsHealthCheck)->passes())->toBeTrue();
});
