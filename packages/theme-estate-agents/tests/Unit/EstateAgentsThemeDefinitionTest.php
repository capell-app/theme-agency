<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\EstateAgents\EstateAgentsThemeServiceProvider;

it('defines the Estate Agents premium theme contract', function (): void {
    $definition = EstateAgentsThemeServiceProvider::definition();

    expect($definition->key)->toBe('estate-agents')
        ->and($definition->package)->toBe('capell-app/theme-estate-agents')
        ->and($definition->extends)->toBe('default')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/estate-agents.css'])
        ->and($definition->tags)->toContain('Property', 'Valuations', 'Local guides')
        ->and($definition->bestFit)->toContain('Estate agencies')
        ->and($definition->includedSections)->toBe([
            'navigation',
            'hero',
            'property-search',
            'featured-properties',
            'valuation-cta',
            'local-guide',
            'agent-team',
            'viewing-request',
            'market-proof',
            'features',
            'proof',
            'content-listing',
            'cta',
            'footer',
        ])
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('estate-agents')
        ->and($definition->presets[0]->values)->toHaveKey('accentColor', '#c7f464');
});

it('renders estate-owned property sections through the registry', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EstateAgentsThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new EstateAgentsThemeServiceProvider($this->app))->boot($registry);

    $heroRenderer = $registry->sectionRenderer('estate-agents', 'hero');
    $searchRenderer = $registry->sectionRenderer('estate-agents', 'property-search');
    $featuredRenderer = $registry->sectionRenderer('estate-agents', 'featured-properties');
    $valuationRenderer = $registry->sectionRenderer('estate-agents', 'valuation-cta');

    assert($heroRenderer instanceof SectionRenderer);
    assert($searchRenderer instanceof SectionRenderer);
    assert($featuredRenderer instanceof SectionRenderer);
    assert($valuationRenderer instanceof SectionRenderer);

    $heroHtml = $heroRenderer->render(HeroSectionData::from([
        'heading' => 'Homes, valuations, and local guidance in one journey',
        'summary' => 'Hydrated estate agency hero copy should render in the property layout.',
        'mediaUrl' => '/images/property-hero.jpg',
        'mediaAlt' => 'Townhouse exterior',
        'actions' => [
            ['label' => 'Search homes', 'url' => '#property-search'],
            ['label' => 'Book valuation', 'url' => '#valuation'],
        ],
    ]));

    $searchHtml = $searchRenderer->render(estateAgentsThemeSection('property-search', [
        'heading' => 'Start with the area',
        'summary' => 'Search copy should stay public and editor-free.',
    ]));

    $featuredHtml = $featuredRenderer->render(estateAgentsThemeSection('featured-properties', [
        'heading' => 'Featured launches',
        'items' => [
            ['title' => 'Garden house', 'summary' => 'Family home with local proof.', 'price' => '725,000', 'meta' => '3 bed'],
        ],
    ]));

    $valuationHtml = $valuationRenderer->render(estateAgentsThemeSection('valuation-cta', [
        'heading' => 'Value before you list',
        'summary' => 'Valuation copy should stay safe.',
    ]));

    expect($heroHtml)
        ->toContain('Homes, valuations, and local guidance in one journey')
        ->toContain('src="/images/property-hero.jpg"')
        ->toContain('fetchpriority="high"')
        ->toContain('Book valuation')
        ->not->toContain('capell-app/theme-estate-agents');

    expect($searchHtml)
        ->toContain('Start with the area')
        ->toContain('Search copy should stay public')
        ->not->toContain('capell-app/theme-estate-agents');

    expect($featuredHtml)
        ->toContain('Featured launches')
        ->toContain('Garden house')
        ->toContain('725,000')
        ->not->toContain('capell-app/theme-estate-agents');

    expect($valuationHtml)
        ->toContain('Value before you list')
        ->toContain('Start valuation')
        ->not->toContain('capell-app/theme-estate-agents');
});

it('passes optional package availability into estate agency sections', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EstateAgentsThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/search');
    CapellCore::forcePackageInstalled('capell-app/address');
    CapellCore::forcePackageInstalled('capell-app/form-builder');

    $registry = new ThemeRegistry;
    (new EstateAgentsThemeServiceProvider($this->app))->boot($registry);

    $searchRenderer = $registry->sectionRenderer('estate-agents', 'property-search');
    $localGuideRenderer = $registry->sectionRenderer('estate-agents', 'local-guide');
    $viewingRenderer = $registry->sectionRenderer('estate-agents', 'viewing-request');

    assert($searchRenderer instanceof SectionRenderer);
    assert($localGuideRenderer instanceof SectionRenderer);
    assert($viewingRenderer instanceof SectionRenderer);

    expect($searchRenderer->render(estateAgentsThemeSection('property-search', [
        'heading' => 'Connected property search',
    ])))
        ->toContain('Search connected')
        ->toContain('Connected property search')
        ->not->toContain('capell-app/theme-estate-agents');

    expect($localGuideRenderer->render(estateAgentsThemeSection('local-guide', [
        'heading' => 'Connected area guide',
    ])))
        ->toContain('Address context connected')
        ->toContain('Connected area guide')
        ->not->toContain('capell-app/theme-estate-agents');

    expect($viewingRenderer->render(estateAgentsThemeSection('viewing-request', [
        'heading' => 'Connected viewing request',
    ])))
        ->toContain('Form Builder connected')
        ->toContain('Connected viewing request')
        ->not->toContain('capell-app/theme-estate-agents');
});

/**
 * @param  array<string, mixed>  $viewData
 */
function estateAgentsThemeSection(string $sectionKey, array $viewData): ThemeSection
{
    return new readonly class($sectionKey, $viewData) implements ThemeSection
    {
        /**
         * @param  array<string, mixed>  $viewData
         */
        public function __construct(
            private string $sectionKey,
            private array $viewData,
        ) {}

        public function key(): string
        {
            return $this->sectionKey;
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return $this->viewData;
        }
    };
}
