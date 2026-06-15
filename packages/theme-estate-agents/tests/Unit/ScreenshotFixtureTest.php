<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\EstateAgents\EstateAgentsThemeServiceProvider;
use Capell\ThemeStudio\EstateAgents\Support\Screenshots\EstateAgentsScreenshotRenderer;

it('renders opt in screenshot fixtures through estate agents Blade sections', function (): void {
    $this->withoutVite();

    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EstateAgentsThemeServiceProvider::$packageName);

    (new EstateAgentsThemeServiceProvider($this->app))->boot(app(ThemeRegistry::class));

    $html = app(EstateAgentsScreenshotRenderer::class)
        ->render('estate-homepage-layout')
        ->render();

    expect($html)
        ->toContain('estate-shell')
        ->toContain('Move with a property team that knows the street')
        ->toContain('Search feels like the front door')
        ->toContain('Book valuation')
        ->not->toContain('capell-app/theme-estate-agents')
        ->not->toContain('authoring')
        ->not->toContain('wire:');
});

it('keeps screenshot fixture routes behind an explicit environment flag', function (): void {
    $provider = file_get_contents(__DIR__ . '/../../src/EstateAgentsThemeServiceProvider.php') ?: '';
    $routes = file_get_contents(__DIR__ . '/../../routes/screenshot-fixtures.php') ?: '';

    expect($provider)
        ->toContain('CAPELL_THEME_ESTATE_AGENTS_SCREENSHOT_FIXTURES_ENABLED')
        ->toContain('loadScreenshotFixtureRoutes')
        ->and($routes)
        ->toContain('/screenshot-fixtures/theme-estate-agents/{screen}');
});
