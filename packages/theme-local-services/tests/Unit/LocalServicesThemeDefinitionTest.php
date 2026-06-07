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
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

uses(PackagesTestCase::class);

it('defines the Local Services theme contract', function (): void {
    $definition = LocalServicesThemeServiceProvider::definition();

    expect($definition->key)->toBe('local-services')
        ->and($definition->package)->toBe('capell-app/theme-local-services')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('proof')
        ->and($definition->includedSections)->toContain('reviews-testimonials')
        ->and($definition->includedSections)->toContain('trust-badges')
        ->and($definition->includedSections)->toContain('opening-hours')
        ->and($definition->includedSections)->toContain('structured-data')
        ->and($definition->includedSections)->toContain('content-listing')
        ->and($definition->includedSections)->toContain('before-after-gallery')
        ->and($definition->includedSections)->toContain('cta')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('renders local SEO structured data reviews and opening hours sections', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $structuredDataRenderer = $registry->sectionRenderer('local-services', 'structured-data');
    $reviewsRenderer = $registry->sectionRenderer('local-services', 'reviews-testimonials');
    $openingHoursRenderer = $registry->sectionRenderer('local-services', 'opening-hours');

    assert($structuredDataRenderer instanceof SectionRenderer);
    assert($reviewsRenderer instanceof SectionRenderer);
    assert($openingHoursRenderer instanceof SectionRenderer);

    $structuredDataHtml = $structuredDataRenderer->render(localServicesThemeSection('structured-data', [
        'business' => [
            'name' => 'Cardiff Boiler Care',
            'url' => 'https://local.example.test',
            'phone' => '+44 29 2000 1234',
            'address' => '12 High Street, Cardiff CF10 1AA',
        ],
        'serviceName' => 'Emergency boiler repair',
        'areaServed' => 'Cardiff',
        'services' => [
            ['title' => 'Boiler repair', 'summary' => 'Emergency heating repairs.'],
        ],
        'faqs' => [
            ['question' => 'Do you offer same-day repairs?', 'answer' => 'Yes, where local route capacity allows.'],
        ],
        'openingHours' => [
            ['dayOfWeek' => 'Monday', 'opens' => '08:00', 'closes' => '18:00'],
        ],
    ]));

    $reviewsHtml = $reviewsRenderer->render(localServicesThemeSection('reviews-testimonials', [
        'heading' => 'What local customers say',
        'items' => [
            ['quote' => 'Arrived inside the promised slot.', 'name' => 'Pontcanna homeowner', 'rating' => 5],
        ],
    ]));

    $hoursHtml = $openingHoursRenderer->render(localServicesThemeSection('opening-hours', [
        'heading' => 'When the quote desk is open',
        'openNow' => true,
        'items' => [
            ['day' => 'Monday to Friday', 'opens' => '08:00', 'closes' => '18:00'],
            ['day' => 'Saturday', 'hours' => 'Emergency callouts'],
        ],
    ]));

    expect($structuredDataHtml)
        ->toContain('application/ld+json')
        ->toContain('"@context":"https://schema.org"')
        ->toContain('"@type":"LocalBusiness"')
        ->toContain('"@type":"Service"')
        ->toContain('"@type":"FAQPage"')
        ->toContain('Cardiff Boiler Care')
        ->toContain('Emergency boiler repair')
        ->toContain('Do you offer same-day repairs?')
        ->not->toContain('capell-app/theme-local-services')
        ->not->toContain('Filament');

    expect($reviewsHtml)
        ->toContain('What local customers say')
        ->toContain('Arrived inside the promised slot.')
        ->toContain('Pontcanna homeowner')
        ->toContain('5 out of 5 stars')
        ->not->toContain('capell-app/theme-local-services');

    expect($hoursHtml)
        ->toContain('When the quote desk is open')
        ->toContain('Open now')
        ->toContain('Monday to Friday')
        ->toContain('08:00–18:00')
        ->toContain('Saturday')
        ->toContain('Emergency callouts')
        ->not->toContain('capell-app/theme-local-services');
});

it('renders every Local Services-owned section anonymously inside the budget without database queries', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/form-builder', false);
    CapellCore::forcePackageInstalled('capell-app/blog', false);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $manifest = localServicesThemeTestManifest();
    $budgetMilliseconds = (float) data_get($manifest, 'performance.frontendRenderBudgetMs', 20);
    $queryCount = 0;

    DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
        if (str_starts_with(strtolower($query->sql), 'select')) {
            $queryCount++;
        }
    });

    foreach (localServicesOwnedSectionKeys() as $sectionKey) {
        $renderer = $registry->sectionRenderer('local-services', $sectionKey);

        assert($renderer instanceof SectionRenderer);

        $section = localServicesThemeSection($sectionKey, localServicesRenderPayload($sectionKey));

        $renderer->render($section);

        $startedAt = hrtime(true);
        $html = $renderer->render($section);
        $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;

        expect($elapsedMilliseconds)->toBeLessThanOrEqual($budgetMilliseconds)
            ->and($html)->not->toContain('capell-app/theme-local-services')
            ->and($html)->not->toContain('Filament')
            ->and($html)->not->toContain('wire:');
    }

    expect($queryCount)->toBe(0);
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

it('renders trust badges and before-after project evidence through the registry', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $trustRenderer = $registry->sectionRenderer('local-services', 'trust-badges');
    $galleryRenderer = $registry->sectionRenderer('local-services', 'before-after-gallery');

    assert($trustRenderer instanceof SectionRenderer);
    assert($galleryRenderer instanceof SectionRenderer);

    $trustHtml = $trustRenderer->render(localServicesThemeSection('trust-badges', [
        'heading' => 'Checked credentials',
        'summary' => 'Proof before the quote request.',
        'items' => [
            [
                'title' => 'Gas Safe registered',
                'summary' => 'Verified engineer status for heating work.',
                'issuer' => 'Gas Safe Register',
                'reference' => '123456',
                'url' => 'https://example.test/accreditations/gas-safe',
                'imageUrl' => 'https://cdn.example.test/gas-safe.svg',
                'imageAlt' => 'Gas Safe badge',
            ],
        ],
    ]));

    $emptyTrustHtml = $trustRenderer->render(localServicesThemeSection('trust-badges', [
        'heading' => 'Empty credentials',
        'items' => [],
    ]));

    $galleryHtml = $galleryRenderer->render(localServicesThemeSection('before-after-gallery', [
        'heading' => 'Visible job outcomes',
        'summary' => 'Before and after work by service route.',
        'items' => [
            [
                'title' => 'Bathroom leak repair',
                'summary' => 'Resolved damp damage and restored the finish.',
                'service' => 'Plumbing',
                'location' => 'Cardiff',
                'url' => '/case-studies/bathroom-leak',
                'beforeImage' => 'https://cdn.example.test/before.jpg',
                'afterImage' => 'https://cdn.example.test/after.jpg',
                'beforeAlt' => 'Damaged bathroom before repair',
                'afterAlt' => 'Bathroom after repair',
            ],
        ],
    ]));

    $emptyGalleryHtml = $galleryRenderer->render(localServicesThemeSection('before-after-gallery', [
        'heading' => 'Empty gallery',
        'items' => [],
    ]));

    expect($trustHtml)
        ->toContain('Checked credentials')
        ->toContain('Proof before the quote request.')
        ->toContain('Gas Safe registered')
        ->toContain('Verified engineer status for heating work.')
        ->toContain('Gas Safe Register · 123456')
        ->toContain('href="https://example.test/accreditations/gas-safe"')
        ->toContain('src="https://cdn.example.test/gas-safe.svg"')
        ->toContain('alt="Gas Safe badge"')
        ->toContain('loading="lazy"')
        ->not->toContain('capell-app/theme-local-services')
        ->not->toContain('Filament')
        ->not->toContain('wire:');

    expect($emptyTrustHtml)
        ->toContain('No trust badges yet')
        ->toContain('Add accreditations, memberships, insurance notes, or review credentials.')
        ->not->toContain('capell-app/theme-local-services');

    expect($galleryHtml)
        ->toContain('Visible job outcomes')
        ->toContain('Before and after work by service route.')
        ->toContain('Bathroom leak repair')
        ->toContain('Resolved damp damage and restored the finish.')
        ->toContain('Plumbing · Cardiff')
        ->toContain('href="/case-studies/bathroom-leak"')
        ->toContain('src="https://cdn.example.test/before.jpg"')
        ->toContain('src="https://cdn.example.test/after.jpg"')
        ->toContain('alt="Damaged bathroom before repair"')
        ->toContain('alt="Bathroom after repair"')
        ->toContain('loading="lazy"')
        ->not->toContain('capell-app/theme-local-services')
        ->not->toContain('Filament')
        ->not->toContain('wire:');

    expect($emptyGalleryHtml)
        ->toContain('No project gallery yet')
        ->toContain('Add before and after project pairs to show visible job outcomes.')
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

it('renders data-driven contact actions without dead or unsafe links', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('local-services', 'contact');

    assert($renderer instanceof SectionRenderer);

    $defaultHtml = $renderer->render(localServicesThemeSection('contact', [
        'heading' => 'Request a quote',
    ]));

    $customHtml = $renderer->render(localServicesThemeSection('contact', [
        'heading' => 'Visit or call',
        'phone' => '+44 29 2000 1234',
        'email' => 'quotes@example.test',
        'address' => '12 High Street, Cardiff CF10 1AA',
        'mapUrl' => 'https://maps.example.test/local-services',
    ]));

    $unsafeHtml = $renderer->render(localServicesThemeSection('contact', [
        'heading' => 'Static contact details',
        'contact' => [
            'phone' => 'Call the desk',
            'email' => 'not-an-email',
            'address' => 'Mobile team only',
            'mapUrl' => 'javascript:alert(1)',
        ],
    ]));

    expect($defaultHtml)
        ->toContain('id="contact"')
        ->toContain('Request a quote')
        ->toContain('Call routing')
        ->toContain('Quote desk')
        ->toContain('Site visits')
        ->toContain('Map')
        ->not->toContain('href="#"')
        ->not->toContain('href=""')
        ->not->toContain('tel:')
        ->not->toContain('mailto:')
        ->not->toContain('capell-app/theme-local-services');

    expect($customHtml)
        ->toContain('Visit or call')
        ->toContain('href="tel:+442920001234"')
        ->toContain('+44 29 2000 1234')
        ->toContain('href="mailto:quotes@example.test"')
        ->toContain('quotes@example.test')
        ->toContain('href="https://maps.example.test/local-services"')
        ->toContain('12 High Street, Cardiff CF10 1AA')
        ->toContain('Open map')
        ->not->toContain('href="#"')
        ->not->toContain('capell-app/theme-local-services');

    expect($unsafeHtml)
        ->toContain('Call the desk')
        ->toContain('Direct urgent, planned, and account enquiries to the right team.')
        ->toContain('not-an-email')
        ->toContain('Mobile team only')
        ->not->toContain('tel:')
        ->not->toContain('mailto:')
        ->not->toContain('javascript:alert')
        ->not->toContain('href="#"');
});

it('renders a real public quote fallback form with configurable submission target', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/form-builder', false);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('local-services', 'quote-form');

    assert($renderer instanceof SectionRenderer);

    $defaultHtml = $renderer->render(localServicesThemeSection('quote-form', [
        'heading' => 'Request a quote',
    ]));

    $customHtml = $renderer->render(localServicesThemeSection('quote-form', [
        'heading' => 'Request a quote',
        'formAction' => '/quotes',
        'formMethod' => 'get',
    ]));

    expect($defaultHtml)
        ->toContain('id="quote"')
        ->toContain('<form')
        ->toContain('action="/contact"')
        ->toContain('method="POST"')
        ->toContain('aria-label="Quote request"')
        ->toContain('name="name"')
        ->toContain('autocomplete="name"')
        ->toContain('name="phone"')
        ->toContain('autocomplete="tel"')
        ->toContain('name="postcode"')
        ->toContain('autocomplete="postal-code"')
        ->toContain('name="service"')
        ->toContain('name="message"')
        ->toContain('Send quote request')
        ->not->toContain('action="#"')
        ->not->toContain('data-field')
        ->not->toContain('capell-app/theme-local-services');

    expect($customHtml)
        ->toContain('action="/quotes"')
        ->toContain('method="GET"')
        ->not->toContain('action="#"')
        ->not->toContain('capell-app/theme-local-services');
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

/**
 * @return array<int, string>
 */
function localServicesOwnedSectionKeys(): array
{
    return array_values(array_filter(
        LocalServicesThemeServiceProvider::definition()->includedSections,
        static fn (string $sectionKey): bool => ! in_array($sectionKey, ['navigation', 'footer'], true),
    ));
}

/**
 * @return array<string, mixed>
 */
function localServicesRenderPayload(string $sectionKey): array
{
    $payload = [
        'heading' => 'Anonymous ' . $sectionKey,
        'summary' => 'Anonymous public render summary.',
        'items' => [],
        'actions' => [],
        'features' => [],
    ];

    if ($sectionKey === 'structured-data') {
        return [
            ...$payload,
            'business' => ['name' => 'Anonymous Local Business'],
            'services' => [['title' => 'Anonymous service']],
            'faqs' => [['question' => 'Anonymous question?', 'answer' => 'Anonymous answer.']],
            'openingHours' => [['dayOfWeek' => 'Monday', 'opens' => '09:00', 'closes' => '17:00']],
        ];
    }

    if ($sectionKey === 'opening-hours') {
        return [
            ...$payload,
            'openNow' => false,
            'items' => [['day' => 'Monday', 'opens' => '09:00', 'closes' => '17:00']],
        ];
    }

    if ($sectionKey === 'reviews-testimonials') {
        return [
            ...$payload,
            'items' => [['quote' => 'Anonymous review.', 'name' => 'Local customer']],
        ];
    }

    if ($sectionKey === 'trust-badges') {
        return [
            ...$payload,
            'items' => [['title' => 'Anonymous accreditation', 'summary' => 'Anonymous credential proof.']],
        ];
    }

    if ($sectionKey === 'before-after-gallery') {
        return [
            ...$payload,
            'items' => [['title' => 'Anonymous project', 'summary' => 'Anonymous project proof.']],
        ];
    }

    return $payload;
}

/**
 * @return array<string, mixed>
 */
function localServicesThemeTestManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Local Services manifest must decode to an array.');

    return $manifest;
}
