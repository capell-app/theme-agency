<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider;

uses(PackagesTestCase::class);

it('defines the Local Services theme contract', function (): void {
    $definition = LocalServicesThemeServiceProvider::definition();

    expect($definition->key)->toBe('local-services')
        ->and($definition->package)->toBe('capell-app/theme-local-services')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('proof')
        ->and($definition->includedSections)->toContain('content-listing')
        ->and($definition->includedSections)->toContain('cta')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('renders standard sections through Local Services views', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $featureRenderer = $registry->sectionRenderer('local-services', 'features');
    $proofRenderer = $registry->sectionRenderer('local-services', 'proof');
    $listingRenderer = $registry->sectionRenderer('local-services', 'content-listing');
    $ctaRenderer = $registry->sectionRenderer('local-services', 'cta');

    assert($featureRenderer instanceof SectionRenderer);
    assert($proofRenderer instanceof SectionRenderer);
    assert($listingRenderer instanceof SectionRenderer);
    assert($ctaRenderer instanceof SectionRenderer);

    $featureHtml = $featureRenderer->render(new FeatureSectionData(
        heading: 'Quote-ready service routes',
        summary: 'Feature cards should look like local jobs and estimate paths.',
        features: [
            ['title' => 'Rapid estimate triage', 'description' => 'Match the right team to each enquiry.', 'type' => 'Dispatch'],
        ],
    ));

    $proofHtml = $proofRenderer->render(ProofSectionData::from([
        'heading' => 'Local proof board',
        'summary' => 'Proof should use service metrics.',
        'items' => [
            ['metric' => '24h', 'label' => 'Response', 'summary' => 'Most enquiries receive a fast first reply.'],
            ['metric' => '34', 'label' => 'Coverage'],
        ],
    ]));

    $listingHtml = $listingRenderer->render(new ContentListingSectionData(
        heading: 'Service route cards',
        summary: 'Listings should carry local area and availability cues.',
        items: [
            ['title' => 'Boiler repair', 'summary' => 'Urgent coverage across nearby districts.', 'type' => 'Repair'],
        ],
    ));

    $ctaHtml = $ctaRenderer->render(new CtaSectionData(
        heading: 'Book the next service slot',
        summary: 'Move visitors from scope to confirmed work.',
        actions: [['label' => 'Request quote', 'url' => '#quote', 'style' => 'primary']],
    ));

    expect($featureHtml)
        ->toContain('Quote-ready service routes')
        ->toContain('Dispatch board')
        ->toContain('Quote')
        ->not->toContain('capell-app/theme-local-services');

    expect($proofHtml)
        ->toContain('Local proof board')
        ->toContain('Local proof')
        ->toContain('Route board')
        ->toContain('Arrival window')
        ->toContain('Completion proof')
        ->toContain('24h')
        ->not->toContain('capell-app/theme-local-services');

    expect($listingHtml)
        ->toContain('Service route cards')
        ->toContain('Boiler repair')
        ->toContain('Open slot')
        ->not->toContain('capell-app/theme-local-services');

    expect($ctaHtml)
        ->toContain('Book the next service slot')
        ->toContain('Bookable work')
        ->toContain('Request quote')
        ->not->toContain('capell-app/theme-local-services');
});

it('renders hydrated hero data through the Local Services hero view', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('local-services', 'hero');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(HeroSectionData::from([
        'heading' => 'Book a service team this week',
        'summary' => 'Hydrated local services hero summary.',
        'actions' => [
            ['label' => 'Request a quote', 'url' => '#quote'],
            ['label' => 'View service areas', 'url' => '#areas'],
        ],
    ]));

    expect($html)
        ->toContain('Quote desk')
        ->toContain('Book a service team this week')
        ->toContain('Hydrated local services hero summary.')
        ->toContain('Request a quote')
        ->toContain('View service areas')
        ->toContain('Live route board')
        ->not->toContain('capell-app/theme-local-services');
});

it('renders new premium local services layouts through the registry', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $quoteEstimatorRenderer = $registry->sectionRenderer('local-services', 'quote-estimator');
    $servicePackagesRenderer = $registry->sectionRenderer('local-services', 'service-packages');
    $localityProofRenderer = $registry->sectionRenderer('local-services', 'locality-proof');

    assert($quoteEstimatorRenderer instanceof SectionRenderer);
    assert($servicePackagesRenderer instanceof SectionRenderer);
    assert($localityProofRenderer instanceof SectionRenderer);

    $quoteEstimatorHtml = $quoteEstimatorRenderer->render(localServicesThemeSection('quote-estimator', [
        'heading' => 'Shape the quote',
        'items' => [
            ['title' => 'Property size', 'summary' => 'Estimate logic grouped around practical scoping.'],
        ],
    ]));

    $servicePackagesHtml = $servicePackagesRenderer->render(localServicesThemeSection('service-packages', [
        'heading' => 'Choose a service package',
        'items' => [
            ['title' => 'Maintenance plan', 'summary' => 'Recurring service bundle with clear value.'],
        ],
    ]));

    $localityProofHtml = $localityProofRenderer->render(localServicesThemeSection('locality-proof', [
        'heading' => 'Local response proof',
        'items' => [
            ['title' => 'Central district', 'summary' => 'Neighbourhood proof for fast local response.'],
        ],
    ]));

    expect($quoteEstimatorHtml)
        ->toContain('Shape the quote')
        ->toContain('Property size')
        ->not->toContain('capell-app/theme-local-services');

    expect($servicePackagesHtml)
        ->toContain('Choose a service package')
        ->toContain('Maintenance plan')
        ->not->toContain('capell-app/theme-local-services');

    expect($localityProofHtml)
        ->toContain('Local response proof')
        ->toContain('Central district')
        ->not->toContain('capell-app/theme-local-services');
});

it('renders translated and data-driven service area links', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('local-services', 'service-areas');
    $contactRenderer = $registry->sectionRenderer('local-services', 'contact');

    assert($renderer instanceof SectionRenderer);
    assert($contactRenderer instanceof SectionRenderer);

    $defaultHtml = $renderer->render(localServicesThemeSection('service-areas', [
        'heading' => 'Coverage areas',
    ]));

    $contactHtml = $contactRenderer->render(localServicesThemeSection('contact', [
        'heading' => 'Request a quote',
    ]));

    $customHtml = $renderer->render(localServicesThemeSection('service-areas', [
        'heading' => 'Live service routes',
        'items' => [
            ['label' => 'Cardiff', 'url' => '/areas/cardiff', 'postcode' => 'CF'],
            ['title' => 'Penarth', 'url' => '/areas/penarth'],
            ['name' => 'Vale rural route', 'postcodePrefix' => 'By request'],
        ],
    ]));

    expect($defaultHtml)
        ->toContain('Coverage areas')
        ->toContain('Central service area Local')
        ->toContain('href="#contact"')
        ->not->toContain('Downtown')
        ->not->toContain('Airport District')
        ->not->toContain('href="#"');

    expect($contactHtml)
        ->toContain('id="contact"')
        ->toContain('Request a quote');

    expect($customHtml)
        ->toContain('Live service routes')
        ->toContain('Cardiff CF')
        ->toContain('href="/areas/cardiff"')
        ->toContain('Penarth')
        ->toContain('href="/areas/penarth"')
        ->toContain('Vale rural route By request')
        ->not->toContain('href="#"')
        ->not->toContain('Central service area');
});

/**
 * @param  array<string, mixed>  $viewData
 */
function localServicesThemeSection(string $key, array $viewData): ThemeSection
{
    return new readonly class($key, $viewData) implements ThemeSection
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
            return ['section' => (object) $this->viewData];
        }
    };
}
