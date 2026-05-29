<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider;

uses(PackagesTestCase::class);

it('defines the Portfolio theme contract', function (): void {
    $definition = PortfolioThemeServiceProvider::definition();

    expect($definition->key)->toBe('portfolio')
        ->and($definition->package)->toBe('capell-app/theme-portfolio')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('renders standard feature data through the Portfolio feature view', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('portfolio', 'features');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(new FeatureSectionData(
        heading: 'Studio capabilities',
        summary: 'Editorial service cards should render with portfolio-specific presentation.',
        features: [
            ['title' => 'Case study systems', 'description' => 'Shape proof into reusable portfolio sections.'],
        ],
    ));

    expect($html)
        ->toContain('Studio capabilities')
        ->toContain('Case study systems')
        ->toContain('Studio system')
        ->toContain('Proof point')
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-app/theme-portfolio');
});
