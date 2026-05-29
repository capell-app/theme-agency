<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
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

it('renders hydrated hero data through the Portfolio hero view', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('portfolio', 'hero');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(HeroSectionData::from([
        'heading' => 'Editorial portfolio system',
        'summary' => 'Hydrated summary copy should shape the hero.',
        'actions' => [
            ['label' => 'Open case study', 'url' => '#case'],
            ['label' => 'Request deck', 'url' => '#deck'],
        ],
    ]));

    expect($html)
        ->toContain('Portfolio signal')
        ->toContain('Editorial portfolio system')
        ->toContain('Hydrated summary copy should shape the hero.')
        ->toContain('Open case study')
        ->toContain('Request deck')
        ->not->toContain('capell-app/theme-portfolio');
});

it('renders portfolio-owned standard sections instead of foundation fallbacks', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $proof = $registry->sectionRenderer('portfolio', 'proof');
    $listing = $registry->sectionRenderer('portfolio', 'content-listing');
    $cta = $registry->sectionRenderer('portfolio', 'cta');

    expect($proof)->not->toBeNull()
        ->and($listing)->not->toBeNull()
        ->and($cta)->not->toBeNull();

    expect($proof->render(new ProofSectionData(
        heading: 'Measured outcomes',
        summary: 'Proof should feel like a creator evidence ledger.',
        items: [
            ['metric' => '42%', 'name' => 'Qualified leads', 'summary' => 'A stronger proof surface supports the case-study story.'],
        ],
    )))
        ->toContain('Evidence ledger')
        ->toContain('42%')
        ->not->toContain('capell-app/theme-portfolio');

    expect($listing->render(new ContentListingSectionData(
        heading: 'Selected work',
        summary: 'Listing cards should feel like an editorial work index.',
        items: [
            ['title' => 'Identity refresh', 'summary' => 'A deep case-study card.', 'type' => 'Case study'],
        ],
    )))
        ->toContain('Work index')
        ->toContain('Identity refresh')
        ->toContain('View case')
        ->not->toContain('capell-app/theme-portfolio');

    expect($cta->render(new CtaSectionData(
        heading: 'Plan the next case study',
        summary: 'Portfolio CTAs should use hydrated copy and actions.',
        actions: [
            ['label' => 'Start a brief', 'url' => '#brief', 'style' => 'primary'],
        ],
    )))
        ->toContain('Final action')
        ->toContain('Start a brief')
        ->not->toContain('Book a strategy call')
        ->not->toContain('capell-app/theme-portfolio');
});
