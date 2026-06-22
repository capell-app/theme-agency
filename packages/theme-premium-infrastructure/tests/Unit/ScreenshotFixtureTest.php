<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PremiumInfrastructure\PremiumInfrastructureThemeServiceProvider;
use Capell\ThemeStudio\PremiumInfrastructure\Support\Screenshots\PremiumInfrastructureScreenshotRenderer;

it('renders route backed premium infrastructure hero media fixtures', function (string $screen, string $expected): void {
    $this->withoutVite();

    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PremiumInfrastructureThemeServiceProvider::$packageName);

    (new PremiumInfrastructureThemeServiceProvider($this->app))->boot(app(ThemeRegistry::class));

    $html = app(PremiumInfrastructureScreenshotRenderer::class)
        ->render($screen)
        ->render();

    expect($html)
        ->toContain('editorial-shell')
        ->toContain($expected)
        ->not->toContain('capell-app/theme-premium-infrastructure')
        ->not->toContain('authoring')
        ->not->toContain('wire:');
})->with([
    'base desktop' => ['hero-base-desktop', 'Infrastructure pages for serious technical buyers'],
    'base mobile' => ['hero-base-mobile', 'Infrastructure pages for serious technical buyers'],
    'video desktop' => ['hero-looping-video-desktop', '<video'],
    'video mobile' => ['hero-looping-video-mobile', '<video'],
    'gif desktop' => ['hero-looping-gif-desktop', 'Animated infrastructure workflow loop'],
    'image only desktop' => ['hero-image-only-desktop', 'Still infrastructure dashboard preview'],
]);

it('keeps premium infrastructure screenshot routes behind an explicit environment flag', function (): void {
    $provider = file_get_contents(__DIR__ . '/../../src/PremiumInfrastructureThemeServiceProvider.php') ?: '';
    $routes = file_get_contents(__DIR__ . '/../../routes/screenshot-fixtures.php') ?: '';

    expect($provider)
        ->toContain('CAPELL_THEME_PREMIUM_INFRASTRUCTURE_SCREENSHOT_FIXTURES_ENABLED')
        ->toContain('loadScreenshotFixtureRoutes')
        ->and($routes)
        ->toContain('/screenshot-fixtures/theme-premium-infrastructure/{screen}');
});

it('declares route backed screenshot coverage for every premium infrastructure hero variation', function (): void {
    $contract = capell_json_file_array(__DIR__ . '/../../docs/screenshots.json');
    $entries = data_get($contract, 'entries', []);

    throw_unless(is_array($entries), RuntimeException::class, 'Premium Infrastructure screenshot entries must be an array.');

    $heroEntries = collect($entries)
        ->filter(static fn (mixed $entry): bool => is_array($entry) && str_starts_with((string) ($entry['id'] ?? ''), 'premium-infrastructure-hero-'))
        ->values();

    expect($heroEntries)->toHaveCount(6)
        ->and(data_get($contract, 'visualProof.heroVariations', []))->toContain('base', 'looping-video', 'looping-gif', 'image-only');

    $roles = $heroEntries
        ->flatMap(function (mixed $entry): array {
            if (! is_array($entry)) {
                throw new RuntimeException('Premium Infrastructure hero screenshot entry must be an array.');
            }

            expect($entry['url'] ?? '')->toStartWith('/screenshot-fixtures/theme-premium-infrastructure/')
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
