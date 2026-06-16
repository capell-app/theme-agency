<?php

declare(strict_types=1);

use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Manifest\ManifestValidator;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Inertia\Facades\CapellInertia;
use Capell\Inertia\Support\CapellInertiaManager;
use Capell\Marketplace\Filament\Pages\ThemeExtensionPage;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\InertiaBookings\Health\ThemeInertiaBookingsHealthCheck;
use Capell\ThemeStudio\InertiaBookings\Manifest\ThemeManagementPageContribution;
use Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaBookingsThemeRenderer;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaPublicBookingRequestRenderer;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

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
        ->and(data_get($manifest, 'product.tier'))->toBe('premium')
        ->and(data_get($manifest, 'healthChecks.0.class'))->toBe(ThemeInertiaBookingsHealthCheck::class)
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

it('promotes buyer safe marketplace screenshots backed by the runner contract', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);
    $screenshotContract = json_decode(File::get($packagePath . '/docs/screenshots.json'), true, flags: JSON_THROW_ON_ERROR);

    $marketplaceScreenshots = $manifest['marketplace']['screenshots'] ?? [];
    $contractEntries = $screenshotContract['entries'] ?? [];
    $expectedMarketplaceScreenshotPaths = [
        'docs/assets/marketplace/extension-card.svg',
        'docs/assets/marketplace/inertia-bookings-request-form.png',
        'docs/assets/marketplace/inertia-bookings-mobile-request.png',
        'docs/assets/marketplace/inertia-bookings-homepage.png',
        'docs/assets/marketplace/inertia-bookings-services.png',
        'docs/assets/marketplace/inertia-bookings-locations.png',
    ];
    $expectedRunnerScreenshotPaths = [
        'docs/screenshots/inertia-bookings-homepage.png',
        'docs/screenshots/inertia-bookings-request-flow.png',
        'docs/screenshots/inertia-bookings-services.png',
        'docs/screenshots/inertia-bookings-locations.png',
        'docs/screenshots/inertia-bookings-mobile-request.png',
    ];

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

    $requiredContractPaths = [];

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required Theme Inertia Bookings screenshot contract entries must have screenshot paths.');

        $packageRelativePath = str_replace('packages/theme-inertia-bookings/', '', $screenshotPath);
        $requiredContractPaths[] = $packageRelativePath;

        expect(File::exists(dirname(__DIR__, 4) . '/' . $screenshotPath))->toBeTrue();
    }

    expect($marketplaceScreenshotPaths)->toBe($expectedMarketplaceScreenshotPaths);
    expect($requiredContractPaths)->toBe($expectedRunnerScreenshotPaths);
});

it('registers the theme, booking renderer, and inertia theme assets when installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(InertiaBookingsThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/inertia-vue-adapter');
    CapellCore::forcePackageInstalled('capell-app/theme-inertia-bookings-vue');

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
        ->and($packageSources)->toBe([])
        ->and((new ThemeInertiaBookingsHealthCheck)->passes())->toBeTrue();
});

it('requires the configured inertia adapter and booking component pack for health', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(InertiaBookingsThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $this->app->instance(ThemeRegistry::class, $registry);

    $provider = new InertiaBookingsThemeServiceProvider($this->app);
    $provider->register();
    $provider->packageBooted();

    $healthCheck = new ThemeInertiaBookingsHealthCheck;

    expect($healthCheck->inertiaBridgeAvailable())->toBeTrue()
        ->and($healthCheck->configuredAdapterInstalled())->toBeFalse()
        ->and($healthCheck->configuredAdapterBookingComponentAvailable())->toBeFalse()
        ->and($healthCheck->themeRegistered())->toBeTrue()
        ->and($healthCheck->bookingRendererBound())->toBeTrue()
        ->and($healthCheck->passes())->toBeFalse();

    CapellCore::forcePackageInstalled('capell-app/inertia-vue-adapter');

    expect($healthCheck->configuredAdapterInstalled())->toBeTrue()
        ->and($healthCheck->configuredAdapterBookingComponentAvailable())->toBeFalse()
        ->and($healthCheck->passes())->toBeFalse();

    CapellCore::forcePackageInstalled('capell-app/theme-inertia-bookings-vue');

    expect($healthCheck->configuredAdapterBookingComponentAvailable())->toBeTrue()
        ->and($healthCheck->passes())->toBeTrue();
});

it('checks the configured react booking component pack for health', function (): void {
    config()->set('capell-inertia.adapter', 'react');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(InertiaBookingsThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/inertia-react-adapter');
    CapellCore::forcePackageInstalled('capell-app/theme-inertia-bookings-react');

    $registry = new ThemeRegistry;
    $this->app->instance(ThemeRegistry::class, $registry);

    $provider = new InertiaBookingsThemeServiceProvider($this->app);
    $provider->register();
    $provider->packageBooted();

    $healthCheck = new ThemeInertiaBookingsHealthCheck;

    expect($healthCheck->configuredAdapterInstalled())->toBeTrue()
        ->and($healthCheck->configuredAdapterBookingComponentAvailable())->toBeTrue()
        ->and($healthCheck->passes())->toBeTrue();
});

it('builds public safe inertia page props from theme page data', function (): void {
    $props = (new InertiaBookingsThemeRenderer)->props(themeInertiaBookingsPage());
    $encodedProps = json_encode($props, JSON_THROW_ON_ERROR);

    expect(data_get($props, 'page.title'))->toBe('Private clinic appointments')
        ->and(data_get($props, 'page.meta.eyebrow'))->toBe('Bookings first')
        ->and(data_get($props, 'theme.key'))->toBe(InertiaBookingsThemeServiceProvider::THEME_KEY)
        ->and(data_get($props, 'page.layout.containers.0.widgets'))->toHaveCount(2)
        ->and(data_get($props, 'page.layout.containers.0.widgets.1.data.content'))->toContain('Physiotherapy')
        ->and($encodedProps)->not->toContain('capell-app/theme-inertia-bookings')
        ->and($encodedProps)->not->toContain('data-capell-authoring')
        ->and($encodedProps)->not->toContain('signed-editor')
        ->and($encodedProps)->not->toContain('model_id');
});

it('renders through the configured Capell Inertia page component', function (): void {
    config()->set('capell-inertia.page_component', 'Capell/Page');

    $inertiaManager = Mockery::mock(CapellInertiaManager::class);
    $inertiaManager->shouldReceive('render')
        ->once()
        ->with('Capell/Page', Mockery::on(static fn (array $props): bool => data_get($props, 'page.title') === 'Private clinic appointments'
                && data_get($props, 'page.layout.containers.0.widgets.0.key') === 'hero'))
        ->andReturn(new Response('<main data-testid="inertia-theme-page">Rendered</main>'));
    CapellInertia::swap($inertiaManager);

    expect((new InertiaBookingsThemeRenderer)->render(themeInertiaBookingsPage()))
        ->toBe('<main data-testid="inertia-theme-page">Rendered</main>');
});

it('renders booking requests through public safe Inertia props', function (): void {
    createThemeInertiaBookingsOptionTables();

    $request = Request::create('/bookings', 'GET', ['timezone' => 'Europe/London']);

    $inertiaManager = Mockery::mock(CapellInertiaManager::class);
    $inertiaManager->shouldReceive('render')
        ->once()
        ->with('Capell/Bookings/Request', Mockery::on(static function (array $props): bool {
            $propKeys = array_keys($props);

            return data_get($props, 'timezone') === 'Europe/London'
                && data_get($props, 'options.services') === []
                && in_array('slots', $propKeys, true)
                && ! in_array('adminUrl', $propKeys, true)
                && ! in_array('model_id', $propKeys, true)
                && ! in_array('signedEditorUrl', $propKeys, true);
        }))
        ->andReturn(new Response('<main data-testid="inertia-booking-request">Rendered</main>'));
    CapellInertia::swap($inertiaManager);

    expect((new InertiaPublicBookingRequestRenderer)->render($request)->getContent())
        ->toBe('<main data-testid="inertia-booking-request">Rendered</main>');
});

function themeInertiaBookingsPage(): ThemePageData
{
    return new ThemePageData(
        title: 'Private clinic appointments',
        brand: new BrandProfileData(
            primaryColor: '#0f766e',
            accentColor: '#f97316',
        ),
        sections: [
            new HeroSectionData(
                heading: 'Book the right appointment',
                eyebrow: 'Bookings first',
                summary: 'Choose the service, clinician, and time without exposing admin tools.',
            ),
            new FeatureSectionData(
                heading: 'Services',
                summary: 'Appointment-led services ready for booking.',
                features: [
                    ['title' => 'Physiotherapy', 'description' => 'Hands-on recovery sessions.'],
                    ['title' => 'Sports therapy', 'description' => 'Performance-focused support.'],
                ],
            ),
        ],
    );
}

function createThemeInertiaBookingsOptionTables(): void
{
    if (! Schema::hasTable('booking_services')) {
        Schema::create('booking_services', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('booking_staff_members')) {
        Schema::create('booking_staff_members', function (Blueprint $table): void {
            $table->id();
            $table->string('display_name');
            $table->string('title')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('booking_locations')) {
        Schema::create('booking_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('physical')->index();
            $table->string('city', 64)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
