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

it('declares route backed fixtures for the required estate agents page set', function (): void {
    $contract = json_decode(
        file_get_contents(__DIR__ . '/../../docs/screenshots.json') ?: '[]',
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($contract), RuntimeException::class, 'Estate Agents screenshot contract must decode to an array.');

    $entries = $contract['entries'] ?? [];

    throw_unless(is_array($entries), RuntimeException::class, 'Estate Agents screenshot entries must be an array.');

    $roles = collect($entries)
        ->flatMap(function (mixed $entry): array {
            if (! is_array($entry)) {
                throw new RuntimeException('Estate Agents screenshot entry must be an array.');
            }

            $pageSetRoles = $entry['pageSetRoles'] ?? [];

            expect($entry['required'] ?? false)->toBeTrue()
                ->and($entry['url'] ?? '')->toStartWith('/screenshot-fixtures/theme-estate-agents/')
                ->and($entry['waitFor'] ?? '')->toBe('.estate-shell')
                ->and($pageSetRoles)->toBeArray()->not->toBeEmpty();

            return is_array($pageSetRoles) ? $pageSetRoles : [];
        })
        ->unique()
        ->values()
        ->all();

    expect($roles)->toContain(
        'homepage',
        'landing-conversion-page',
        'list-with-pagination',
        'search-results',
        'contact-conversion-form',
        'detail-resource-page',
    );
});
