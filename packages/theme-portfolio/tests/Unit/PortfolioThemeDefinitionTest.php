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
use Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

uses(PackagesTestCase::class);

it('defines the Portfolio theme contract', function (): void {
    $definition = PortfolioThemeServiceProvider::definition();

    expect($definition->key)->toBe('portfolio')
        ->and($definition->package)->toBe('capell-app/theme-portfolio')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('gallery-lightbox')
        ->and($definition->includedSections)->toContain('resume-cv')
        ->and($definition->includedSections)->toContain('client-logos')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[1]->key)->toBe('portfolio-dark')
        ->and($definition->presets[1]->values)->toHaveKey('colorScheme', 'dark');
});

it('renders standard feature data through the Portfolio feature view', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('portfolio', 'features');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

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
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(HeroSectionData::from([
        'heading' => 'Editorial portfolio system',
        'summary' => 'Hydrated summary copy should shape the hero.',
        'mediaUrl' => '/images/portfolio-hero.jpg',
        'mediaAlt' => 'Portfolio case study wall',
        'actions' => [
            ['label' => 'Open case study', 'url' => '#case'],
            ['label' => 'Request deck', 'url' => '#deck'],
        ],
    ]));

    expect($html)
        ->toContain('Portfolio signal')
        ->toContain('Editorial portfolio system')
        ->toContain('Hydrated summary copy should shape the hero.')
        ->toContain('src="/images/portfolio-hero.jpg"')
        ->toContain('alt="Portfolio case study wall"')
        ->toContain('width="960"')
        ->toContain('height="720"')
        ->toContain('loading="eager"')
        ->toContain('fetchpriority="high"')
        ->toContain('sizes="(min-width: 1024px) 48vw, 100vw"')
        ->toContain('+42%')
        ->toContain('120+')
        ->toContain('6h')
        ->toContain('Open case study')
        ->toContain('Request deck')
        ->not->toContain('capell-app/theme-portfolio');
});

it('renders service and work-grid empty states from the shared placeholder strategy', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $servicesRenderer = $registry->sectionRenderer('portfolio', 'services');
    $workGridRenderer = $registry->sectionRenderer('portfolio', 'work-grid');

    assert($servicesRenderer instanceof SectionRenderer);
    assert($workGridRenderer instanceof SectionRenderer);

    $servicesHtml = $servicesRenderer->render(portfolioThemeSection('services', [
        'heading' => 'Build the offer',
    ]));

    $workGridHtml = $workGridRenderer->render(portfolioThemeSection('work-grid', [
        'heading' => 'Selected projects',
    ]));

    expect($servicesHtml)
        ->toContain('What we build')
        ->toContain('A modular services layer built for portfolio storytelling that converts attention into action.')
        ->toContain('Premium layout ready')
        ->toContain('Add section content to populate this premium layout.')
        ->not->toContain('Brand systems')
        ->not->toContain('capell-app/theme-portfolio');

    expect($workGridHtml)
        ->toContain('30+ Projects')
        ->toContain('12+ Industries')
        ->toContain('97% Retention')
        ->toContain('Premium layout ready')
        ->toContain('Add section content to populate this premium layout.')
        ->not->toContain('Landing suite')
        ->not->toContain('capell-app/theme-portfolio');
});

it('renders portfolio-owned standard sections instead of foundation fallbacks', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/content-sections');

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $proof = $registry->sectionRenderer('portfolio', 'proof');
    $listing = $registry->sectionRenderer('portfolio', 'content-listing');
    $cta = $registry->sectionRenderer('portfolio', 'cta');
    $caseStudies = $registry->sectionRenderer('portfolio', 'case-studies');

    expect($proof)->not->toBeNull()
        ->and($listing)->not->toBeNull()
        ->and($cta)->not->toBeNull()
        ->and($caseStudies)->not->toBeNull();

    assert($proof instanceof SectionRenderer);
    assert($listing instanceof SectionRenderer);
    assert($cta instanceof SectionRenderer);
    assert($caseStudies instanceof SectionRenderer);

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

    expect($caseStudies->render(new readonly class implements ThemeSection
    {
        public function key(): string
        {
            return 'case-studies';
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
            return [
                'heading' => 'Case-study operating system',
                'summary' => 'Case sections should show scope, role, timeline, outcomes, and assets.',
                'items' => [
                    [
                        'title' => 'Launch narrative rebuild',
                        'summary' => 'A premium studio case file with measurable outcomes.',
                        'type' => 'Case study',
                        'metric' => '+58%',
                        'scope' => 'Strategy / Story / Design',
                        'role' => 'Studio lead',
                        'timeline' => '5 weeks',
                    ],
                ],
            ];
        }
    }))
        ->toContain('Case file system')
        ->toContain('Case-study operating system')
        ->toContain('Launch narrative rebuild')
        ->toContain('Scope')
        ->toContain('Studio lead')
        ->toContain('5 weeks')
        ->toContain('+58%')
        ->not->toContain('capell-app/theme-portfolio');
});

it('renders new premium portfolio layouts through the registry', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $caseStudyDetailRenderer = $registry->sectionRenderer('portfolio', 'case-study-detail');
    $processRenderer = $registry->sectionRenderer('portfolio', 'process');
    $availabilityRenderer = $registry->sectionRenderer('portfolio', 'availability');

    assert($caseStudyDetailRenderer instanceof SectionRenderer);
    assert($processRenderer instanceof SectionRenderer);
    assert($availabilityRenderer instanceof SectionRenderer);

    $caseStudyDetailHtml = $caseStudyDetailRenderer->render(portfolioThemeSection('case-study-detail', [
        'heading' => 'Inside the case study',
        'items' => [
            ['title' => 'Conversion lift', 'summary' => 'Detailed proof for the case-study narrative.'],
        ],
    ]));

    $processHtml = $processRenderer->render(portfolioThemeSection('process', [
        'heading' => 'How the engagement runs',
        'items' => [
            ['title' => 'Discovery sprint', 'summary' => 'A clear working rhythm for premium engagements.'],
        ],
    ]));

    $availabilityHtml = $availabilityRenderer->render(portfolioThemeSection('availability', [
        'heading' => 'Book the next slot',
        'items' => [
            ['title' => 'Advisory week', 'summary' => 'Availability framed around a real engagement window.'],
        ],
    ]));

    expect($caseStudyDetailHtml)
        ->toContain('Inside the case study')
        ->toContain('Conversion lift')
        ->not->toContain('capell-app/theme-portfolio');

    expect($processHtml)
        ->toContain('How the engagement runs')
        ->toContain('Discovery sprint')
        ->not->toContain('capell-app/theme-portfolio');

    expect($availabilityHtml)
        ->toContain('Book the next slot')
        ->toContain('Advisory week')
        ->not->toContain('capell-app/theme-portfolio');
});

it('renders newsletter chrome from translations and avoids inert forms', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('portfolio', 'newsletter');

    assert($renderer instanceof SectionRenderer);

    $staticHtml = $renderer->render(portfolioThemeSection('newsletter', [
        'heading' => 'Join the studio notes',
    ]));

    $formHtml = $renderer->render(portfolioThemeSection('newsletter', [
        'heading' => 'Join the studio notes',
        'formAction' => '/newsletter/subscribe',
        'formMethod' => 'post',
    ]));

    expect($staticHtml)
        ->toContain('Join the studio notes')
        ->toContain('Connect a newsletter form action to capture subscribers.')
        ->not->toContain('action="#"')
        ->not->toContain('Subscribe</button>');

    expect($formHtml)
        ->toContain('action="/newsletter/subscribe"')
        ->toContain('method="POST"')
        ->toContain('aria-label="Newsletter signup"')
        ->toContain('placeholder="you@company.com"')
        ->toContain('Subscribe')
        ->not->toContain('action="#"')
        ->not->toContain('capell-app/theme-portfolio');
});

it('renders footer services testimonials speaking and newsletter sections with hydrated or empty data', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $footerRenderer = $registry->sectionRenderer('portfolio', 'footer');
    $servicesRenderer = $registry->sectionRenderer('portfolio', 'services');
    $testimonialsRenderer = $registry->sectionRenderer('portfolio', 'testimonials');
    $speakingRenderer = $registry->sectionRenderer('portfolio', 'speaking-media-kit');
    $newsletterRenderer = $registry->sectionRenderer('portfolio', 'newsletter');

    assert($footerRenderer instanceof SectionRenderer);
    assert($servicesRenderer instanceof SectionRenderer);
    assert($testimonialsRenderer instanceof SectionRenderer);
    assert($speakingRenderer instanceof SectionRenderer);
    assert($newsletterRenderer instanceof SectionRenderer);

    $footerHtml = $footerRenderer->render(portfolioThemeSection('footer', [
        'heading' => 'Studio footer',
        'summary' => 'Footer copy stays public-safe.',
    ]));

    $servicesHtml = $servicesRenderer->render(portfolioThemeSection('services', [
        'heading' => 'Services',
        'items' => [
            ['type' => 'Strategy', 'title' => 'Positioning sprint', 'summary' => 'Hydrated offer card.'],
        ],
    ]));

    $testimonialsHtml = $testimonialsRenderer->render(portfolioThemeSection('testimonials', [
        'heading' => 'Client proof',
        'items' => [
            ['quote' => 'Hydrated testimonial quote.', 'attribution' => 'Studio client'],
        ],
    ]));

    $speakingHtml = $speakingRenderer->render(portfolioThemeSection('speaking-media-kit', [
        'heading' => 'Media kit',
        'items' => [
            ['title' => 'Podcast profile', 'summary' => 'Hydrated media-kit card.'],
        ],
    ]));

    $newsletterHtml = $newsletterRenderer->render(portfolioThemeSection('newsletter', [
        'heading' => 'Creator notes',
        'formAction' => '/newsletter/capture',
    ]));

    expect($footerHtml)
        ->toContain('Studio footer')
        ->toContain('Footer copy stays public-safe.')
        ->not->toContain('capell-app/theme-portfolio');

    expect($servicesHtml)
        ->toContain('Positioning sprint')
        ->toContain('Hydrated offer card.')
        ->not->toContain('Premium layout ready')
        ->not->toContain('capell-app/theme-portfolio');

    expect($testimonialsHtml)
        ->toContain('Client proof')
        ->toContain('Hydrated testimonial quote.')
        ->toContain('Studio client')
        ->not->toContain('Premium layout ready')
        ->not->toContain('capell-app/theme-portfolio');

    expect($speakingHtml)
        ->toContain('Media kit')
        ->toContain('Podcast profile')
        ->toContain('Hydrated media-kit card.')
        ->not->toContain('Premium layout ready')
        ->not->toContain('capell-app/theme-portfolio');

    expect($newsletterHtml)
        ->toContain('Creator notes')
        ->toContain('action="/newsletter/capture"')
        ->toContain('Subscribe')
        ->not->toContain('action="#"')
        ->not->toContain('capell-app/theme-portfolio');
});

it('renders gallery resume and client-logo sections with hydrated or empty data', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $galleryRenderer = $registry->sectionRenderer('portfolio', 'gallery-lightbox');
    $resumeRenderer = $registry->sectionRenderer('portfolio', 'resume-cv');
    $logosRenderer = $registry->sectionRenderer('portfolio', 'client-logos');

    assert($galleryRenderer instanceof SectionRenderer);
    assert($resumeRenderer instanceof SectionRenderer);
    assert($logosRenderer instanceof SectionRenderer);

    $galleryHtml = $galleryRenderer->render(portfolioThemeSection('gallery-lightbox', [
        'heading' => 'Visual proof library',
        'items' => [
            [
                'type' => 'Campaign image',
                'title' => 'Launch visual system',
                'summary' => 'Gallery item with inspectable imagery.',
                'imageUrl' => 'https://cdn.example.test/gallery.jpg',
                'imageAlt' => 'Launch visual system preview',
                'url' => '/work/launch-visual-system',
            ],
        ],
    ]));

    $resumeHtml = $resumeRenderer->render(portfolioThemeSection('resume-cv', [
        'heading' => 'Selected credentials',
        'downloadAction' => ['label' => 'Download CV', 'url' => '/resume.pdf'],
        'items' => [
            [
                'period' => '2022-present',
                'title' => 'Principal consultant',
                'organization' => 'Studio Practice',
                'summary' => 'Led creator-positioning and case-study systems.',
            ],
        ],
    ]));

    $logosHtml = $logosRenderer->render(portfolioThemeSection('client-logos', [
        'heading' => 'Trusted by focused teams',
        'items' => [
            [
                'name' => 'Northstar Labs',
                'logo' => 'https://cdn.example.test/northstar.svg',
                'alt' => 'Northstar Labs logo',
            ],
            [
                'name' => 'Plain text client',
            ],
        ],
    ]));

    $emptyGalleryHtml = $galleryRenderer->render(portfolioThemeSection('gallery-lightbox', [
        'heading' => 'Empty gallery',
        'items' => [],
    ]));

    expect($galleryHtml)
        ->toContain('Visual proof library')
        ->toContain('Launch visual system')
        ->toContain('Gallery item with inspectable imagery.')
        ->toContain('src="https://cdn.example.test/gallery.jpg"')
        ->toContain('alt="Launch visual system preview"')
        ->toContain('href="/work/launch-visual-system"')
        ->toContain('loading="lazy"')
        ->not->toContain('capell-app/theme-portfolio')
        ->not->toContain('Filament')
        ->not->toContain('wire:');

    expect($resumeHtml)
        ->toContain('Selected credentials')
        ->toContain('href="/resume.pdf"')
        ->toContain('Download CV')
        ->toContain('2022-present')
        ->toContain('Principal consultant')
        ->toContain('Studio Practice')
        ->toContain('Led creator-positioning and case-study systems.')
        ->not->toContain('capell-app/theme-portfolio');

    expect($logosHtml)
        ->toContain('Trusted by focused teams')
        ->toContain('src="https://cdn.example.test/northstar.svg"')
        ->toContain('alt="Northstar Labs logo"')
        ->toContain('Plain text client')
        ->not->toContain('capell-app/theme-portfolio');

    expect($emptyGalleryHtml)
        ->toContain('Empty gallery')
        ->toContain('Premium layout ready')
        ->toContain('Add section content to populate this premium layout.')
        ->not->toContain('capell-app/theme-portfolio');
});

it('uses the shared placeholder for empty portfolio-owned content sections', function (string $sectionKey): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('portfolio', $sectionKey);

    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(portfolioThemeSection($sectionKey, [
        'heading' => 'Empty ' . $sectionKey,
        'items' => [],
    ]));

    expect($html)
        ->toContain('Empty ' . $sectionKey)
        ->toContain('Premium layout ready')
        ->toContain('Add section content to populate this premium layout.')
        ->not->toContain('capell-app/theme-portfolio');
})->with([
    'case-studies',
    'work-grid',
    'services',
    'gallery-lightbox',
    'resume-cv',
    'client-logos',
    'testimonials',
    'speaking-media-kit',
]);

it('renders every Portfolio-owned section anonymously inside the budget without database queries', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/content-sections', false);
    CapellCore::forcePackageInstalled('capell-app/media-library', false);
    CapellCore::forcePackageInstalled('capell-app/newsletter', false);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $manifest = portfolioThemeTestManifest();
    $budgetMilliseconds = portfolioFrontendRenderBudgetMs($manifest);
    $queryCount = 0;

    DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
        if (str_starts_with(strtolower($query->sql), 'select')) {
            $queryCount++;
        }
    });

    foreach (portfolioOwnedSectionKeys() as $sectionKey) {
        $renderer = $registry->sectionRenderer('portfolio', $sectionKey);

        assert($renderer instanceof SectionRenderer);

        $section = portfolioThemeSection($sectionKey, portfolioRenderPayload($sectionKey));

        $renderer->render($section);

        $startedAt = hrtime(true);
        $html = $renderer->render($section);
        $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;

        expect($elapsedMilliseconds)->toBeLessThanOrEqual($budgetMilliseconds)
            ->and($html)->not->toContain('capell-app/theme-portfolio')
            ->and($html)->not->toContain('Filament')
            ->and($html)->not->toContain('wire:');
    }

    expect($queryCount)->toBe(0);
});

/**
 * @param  array<string, mixed>  $viewData
 */
function portfolioThemeSection(string $key, array $viewData): ThemeSection
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
            return array_merge($this->viewData, ['section' => (object) $this->viewData]);
        }
    };
}

/**
 * @return array<int, string>
 */
function portfolioOwnedSectionKeys(): array
{
    return array_values(array_filter(
        PortfolioThemeServiceProvider::definition()->includedSections,
        static fn (string $sectionKey): bool => $sectionKey !== 'navigation',
    ));
}

/**
 * @return array<string, mixed>
 */
function portfolioRenderPayload(string $sectionKey): array
{
    $payload = [
        'heading' => 'Anonymous ' . $sectionKey,
        'summary' => 'Anonymous public render summary.',
        'items' => [
            ['title' => 'Anonymous portfolio item', 'summary' => 'Anonymous portfolio item summary.'],
        ],
        'actions' => [
            ['label' => 'Anonymous action', 'url' => '#action'],
        ],
    ];

    if ($sectionKey === 'hero') {
        return [
            ...$payload,
            'mediaUrl' => 'https://cdn.example.test/portfolio-hero.jpg',
            'mediaAlt' => 'Anonymous portfolio hero',
        ];
    }

    if ($sectionKey === 'newsletter') {
        return [
            ...$payload,
            'formAction' => '/newsletter/subscribe',
            'formMethod' => 'post',
        ];
    }

    return $payload;
}

/**
 * @return array<string, mixed>
 */
function portfolioThemeTestManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Portfolio manifest must decode to an array.');

    $stringKeyedManifest = [];

    foreach ($manifest as $key => $value) {
        if (is_string($key)) {
            $stringKeyedManifest[$key] = $value;
        }
    }

    return $stringKeyedManifest;
}

/**
 * @param  array<string, mixed>  $manifest
 */
function portfolioFrontendRenderBudgetMs(array $manifest): float
{
    $budget = data_get($manifest, 'performance.frontendRenderBudgetMs', 20);

    return is_numeric($budget) ? (float) $budget : 20.0;
}
