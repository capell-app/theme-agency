<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PremiumProductStory\PremiumProductStoryThemeServiceProvider;
use Capell\ThemeStudio\PremiumProductStory\Support\Screenshots\PremiumProductStoryScreenshotRenderer;

it('renders route backed premium product story hero media fixtures', function (string $screen, string $expected): void {
    $this->withoutVite();

    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PremiumProductStoryThemeServiceProvider::$packageName);

    (new PremiumProductStoryThemeServiceProvider($this->app))->boot(app(ThemeRegistry::class));

    $html = app(PremiumProductStoryScreenshotRenderer::class)
        ->render($screen)
        ->render();

    expect($html)
        ->toContain('product-shell')
        ->toContain($expected)
        ->not->toContain('capell-app/theme-premium-product-story')
        ->not->toContain('authoring')
        ->not->toContain('wire:');
})->with([
    'base desktop' => ['hero-base-desktop', 'The launch page with no wasted motion'],
    'base mobile' => ['hero-base-mobile', 'The launch page with no wasted motion'],
    'video desktop' => ['hero-looping-video-desktop', '<video'],
    'video mobile' => ['hero-looping-video-mobile', '<video'],
    'gif desktop' => ['hero-looping-gif-desktop', 'Animated product feature loop'],
    'image only desktop' => ['hero-image-only-desktop', 'Still product preview'],
]);

it('keeps premium product story screenshot routes behind an explicit environment flag', function (): void {
    $provider = file_get_contents(__DIR__ . '/../../src/PremiumProductStoryThemeServiceProvider.php') ?: '';
    $routes = file_get_contents(__DIR__ . '/../../routes/screenshot-fixtures.php') ?: '';

    expect($provider)
        ->toContain('CAPELL_THEME_PREMIUM_PRODUCT_STORY_SCREENSHOT_FIXTURES_ENABLED')
        ->toContain('loadScreenshotFixtureRoutes')
        ->and($routes)
        ->toContain('/screenshot-fixtures/theme-premium-product-story/{screen}');
});

it('declares route backed screenshot coverage for every premium product story hero variation', function (): void {
    $contract = capell_json_file_array(__DIR__ . '/../../docs/screenshots.json');
    $entries = data_get($contract, 'entries', []);

    throw_unless(is_array($entries), RuntimeException::class, 'Premium Product Story screenshot entries must be an array.');

    $heroEntries = collect($entries)
        ->filter(static fn (mixed $entry): bool => is_array($entry) && is_string($entryId = $entry['id'] ?? null) && str_starts_with($entryId, 'premium-product-story-hero-'))
        ->values();

    expect($heroEntries)->toHaveCount(6)
        ->and(data_get($contract, 'visualProof.heroVariations', []))->toContain('base', 'looping-video', 'looping-gif', 'image-only');

    $roles = $heroEntries
        ->flatMap(function (mixed $entry): array {
            if (! is_array($entry)) {
                throw new RuntimeException('Premium Product Story hero screenshot entry must be an array.');
            }

            expect($entry['url'] ?? '')->toStartWith('/screenshot-fixtures/theme-premium-product-story/')
                ->and($entry['scenario'] ?? '')->toBe('frontend-page')
                ->and($entry['waitFor'] ?? '')->toBe('.product-shell')
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
