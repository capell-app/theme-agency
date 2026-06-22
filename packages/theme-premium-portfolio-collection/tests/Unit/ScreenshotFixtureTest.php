<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PremiumPortfolioCollection\PremiumPortfolioCollectionThemeServiceProvider;
use Capell\ThemeStudio\PremiumPortfolioCollection\Support\Screenshots\PremiumPortfolioCollectionScreenshotRenderer;

it('renders route backed premium portfolio collection hero media fixtures', function (string $screen, string $expected): void {
    $this->withoutVite();

    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PremiumPortfolioCollectionThemeServiceProvider::$packageName);

    (new PremiumPortfolioCollectionThemeServiceProvider($this->app))->boot(app(ThemeRegistry::class));

    $html = app(PremiumPortfolioCollectionScreenshotRenderer::class)
        ->render($screen)
        ->render();

    expect($html)
        ->toContain('editorial-shell')
        ->toContain($expected)
        ->not->toContain('capell-app/theme-premium-portfolio-collection')
        ->not->toContain('authoring')
        ->not->toContain('wire:');
})->with([
    'base desktop' => ['hero-base-desktop', 'A curated first screen for standout work'],
    'base mobile' => ['hero-base-mobile', 'A curated first screen for standout work'],
    'video desktop' => ['hero-looping-video-desktop', '<video'],
    'video mobile' => ['hero-looping-video-mobile', '<video'],
    'gif desktop' => ['hero-looping-gif-desktop', 'Animated portfolio preview loop'],
    'image only desktop' => ['hero-image-only-desktop', 'Still portfolio preview image'],
]);

it('keeps premium portfolio collection screenshot routes behind an explicit environment flag', function (): void {
    $provider = file_get_contents(__DIR__ . '/../../src/PremiumPortfolioCollectionThemeServiceProvider.php') ?: '';
    $routes = file_get_contents(__DIR__ . '/../../routes/screenshot-fixtures.php') ?: '';

    expect($provider)
        ->toContain('CAPELL_THEME_PREMIUM_PORTFOLIO_COLLECTION_SCREENSHOT_FIXTURES_ENABLED')
        ->toContain('loadScreenshotFixtureRoutes')
        ->and($routes)
        ->toContain('/screenshot-fixtures/theme-premium-portfolio-collection/{screen}');
});

it('declares route backed screenshot coverage for every premium portfolio collection hero variation', function (): void {
    $contract = capell_json_file_array(__DIR__ . '/../../docs/screenshots.json');
    $entries = data_get($contract, 'entries', []);

    throw_unless(is_array($entries), RuntimeException::class, 'Premium Portfolio Collection screenshot entries must be an array.');

    $heroEntries = collect($entries)
        ->filter(static fn (mixed $entry): bool => is_array($entry) && str_starts_with((string) ($entry['id'] ?? ''), 'premium-portfolio-collection-hero-'))
        ->values();

    expect($heroEntries)->toHaveCount(6)
        ->and(data_get($contract, 'visualProof.heroVariations', []))->toContain('base', 'looping-video', 'looping-gif', 'image-only');

    $roles = $heroEntries
        ->flatMap(function (mixed $entry): array {
            if (! is_array($entry)) {
                throw new RuntimeException('Premium Portfolio Collection hero screenshot entry must be an array.');
            }

            expect($entry['url'] ?? '')->toStartWith('/screenshot-fixtures/theme-premium-portfolio-collection/')
                ->and($entry['scenario'] ?? '')->toBe('frontend-page')
                ->and($entry['waitFor'] ?? '')->toBe('.editorial-shell')
                ->and($entry['viewport'] ?? null)->toBeArray()
                ->and($entry['colorSchemes'] ?? [])->toContain('light');

            $pageSetRoles = $entry['pageSetRoles'] ?? [];

            return is_array($pageSetRoles) ? $pageSetRoles : [];
        })
        ->unique()
        ->values()
        ->all();

    expect($roles)->toContain('hero-base', 'hero-video', 'hero-gif', 'hero-image-fallback', 'desktop', 'mobile', 'header-image');
});
