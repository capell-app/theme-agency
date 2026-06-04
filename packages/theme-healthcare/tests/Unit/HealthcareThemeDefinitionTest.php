<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\FooterData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Healthcare\Health\ThemeHealthcareHealthCheck;
use Capell\ThemeStudio\Healthcare\HealthcareThemeServiceProvider;
use Capell\ThemeStudio\Healthcare\Rendering\BlogTeaserSectionRenderer;
use Capell\ThemeStudio\Healthcare\Rendering\BookingSectionRenderer;
use Capell\ThemeStudio\Healthcare\Rendering\EventPanelSectionRenderer;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

it('defines the healthcare premium renderer contract', function (): void {
    $definition = HealthcareThemeServiceProvider::definition();
    $manifest = json_decode((string) file_get_contents(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($definition->package)->toBe('capell-app/theme-healthcare')
        ->and($definition->key)->toBe(HealthcareThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Healthcare')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/healthcare.css'])
        ->and($definition->includedSections)->toBe([
            'utility-bar',
            'navigation',
            'hero',
            'features',
            'content-listing',
            'service-finder',
            'services',
            'care-pathway',
            'clinicians',
            'booking',
            'locations',
            'insurance-trust',
            'events',
            'proof',
            'comparison',
            'blog-teaser',
            'contact',
            'cta',
            'footer',
        ])
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->tags)->toBe(['Healthcare', 'Appointments', 'Services'])
        ->and($manifest['product']['tier'])->toBe('premium')
        ->and($manifest['commands']['demo'])->toBe('capell:theme-healthcare-demo')
        ->and($manifest['commands']['demoParams'])->toBe(['url', 'languages', 'sites'])
        ->and($manifest['healthChecks'][0]['class'])->toBe(ThemeHealthcareHealthCheck::class)
        ->and(ThemeHealthcareHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('declares renderers for every healthcare and fallback section', function (): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');

    $provider = new HealthcareThemeServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'sectionRenderers');

    expect(array_keys($method->invoke($provider)))->toBe([
        'utility-bar',
        'navigation',
        'hero',
        'features',
        'content-listing',
        'service-finder',
        'services',
        'care-pathway',
        'clinicians',
        'booking',
        'locations',
        'insurance-trust',
        'events',
        'comparison',
        'proof',
        'blog-teaser',
        'contact',
        'cta',
        'footer',
    ]);
});

it('registers healthcare only when the theme package is installed', function (): void {
    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);

    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName, false);
    $provider->boot($registry);

    expect($registry->has('healthcare'))->toBeFalse();

    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $provider->boot($registry);

    expect($registry->has('healthcare'))->toBeTrue()
        ->and($registry->definition('healthcare')->package)->toBe(HealthcareThemeServiceProvider::$packageName);
});

it('registers healthcare tailwind imports and blade sources when installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(fn (mixed $asset): bool => $asset->packageName === HealthcareThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(fn (mixed $asset): bool => $asset->packageName === HealthcareThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain('resources/css/theme-healthcare.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php');
});

it('renders public healthcare markup without forbidden package or authoring tokens', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $html = $registry->renderer('healthcare')->render(new ThemePageData(
        title: 'Aster Clinic',
        brand: new BrandProfileData,
        sections: [
            healthcareThemeSection('utility-bar', [
                'summary' => 'Same-week appointments available.',
                'items' => [['label' => 'Call 02920 000000', 'url' => '/contact']],
            ]),
            new HeroSectionData(
                heading: 'Specialist care with calm, clear next steps',
                eyebrow: 'Private healthcare',
                summary: 'Appointment-led pages for services, clinicians, resources, and locations.',
                mediaUrl: '/images/clinic-hero.jpg',
                mediaAlt: 'Clinician consultation room',
                actions: [['label' => 'Book appointment', 'url' => '/appointments']],
            ),
            healthcareThemeSection('service-finder', [
                'heading' => 'Find the right care',
                'items' => [['group' => 'Need', 'options' => ['GP', 'Diagnostics', 'Physiotherapy']]],
            ]),
            healthcareThemeSection('services', [
                'heading' => 'Clinical services',
                'items' => [['title' => 'Health assessments', 'summary' => 'Structured checks and plans.', 'image' => '/images/service.jpg', 'imageAlt' => 'Consultation room']],
            ]),
            healthcareThemeSection('clinicians', [
                'heading' => 'Meet the clinicians',
                'items' => [['title' => 'Dr Amara Patel', 'summary' => 'Consultant physician.', 'image' => '/images/clinician.jpg', 'imageAlt' => 'Dr Amara Patel']],
            ]),
            healthcareThemeSection('booking', [
                'heading' => 'Request an appointment',
                'items' => [['title' => 'Same-week triage']],
            ]),
            healthcareThemeSection('events', [
                'heading' => 'Care sessions',
                'items' => [['title' => 'Heart health evening', 'summary' => 'Consultant-led Q&A.']],
            ]),
            new ProofSectionData(
                heading: 'Trusted by patients',
                items: [['metric' => '98%', 'name' => 'Patient satisfaction']],
            ),
            healthcareThemeSection('comparison', [
                'heading' => 'Compare care pathways',
                'items' => [['title' => 'Rapid access', 'summary' => 'Initial consultation and referral plan.']],
            ]),
            healthcareThemeSection('blog-teaser', [
                'heading' => 'Care resources',
                'items' => [['title' => 'Preparing for a first consultation', 'summary' => 'What to bring and expect.', 'url' => '/resources/first-consultation']],
            ]),
            healthcareThemeSection('contact', [
                'heading' => 'Locations',
                'items' => [['title' => 'Cardiff clinic', 'address' => 'Central Cardiff', 'phone' => '02920 000000']],
            ]),
            new CtaSectionData(
                heading: 'Start with the right appointment',
                actions: [['label' => 'Book appointment', 'url' => '/appointments']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Aster Clinic',
            items: [['label' => 'Services', 'url' => '/services']],
            ctaLabel: 'Book',
            ctaUrl: '/appointments',
        ),
        footer: new FooterData(
            brandName: 'Aster Clinic',
            columns: [
                ['heading' => 'Care', 'links' => [['label' => 'Services', 'url' => '/services']]],
            ],
        ),
    ));

    expect($html)
        ->toContain('Aster Clinic')
        ->toContain('href="#main-content"')
        ->toContain('id="main-content"')
        ->toContain('fetchpriority="high"')
        ->toContain('decoding="async"')
        ->toContain('loading="lazy"')
        ->toContain('width="1200"')
        ->toContain('width="800"')
        ->toContain('Previous items')
        ->toContain('Next items')
        ->toContain('Clinical trust')
        ->toContain('Safety review')
        ->toContain('Escalation route')
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-theme')
        ->not->toContain('capell-app/theme-healthcare')
        ->not->toContain('theme-healthcare')
        ->not->toContain('signed')
        ->not->toContain('filament')
        ->not->toContain('editor')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('keeps healthcare typography defaults low specificity so utility colors can win', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-healthcare.css');

    expect($css)
        ->toContain(':where(.healthcare-shell h1, .healthcare-shell h2, .healthcare-shell h3)')
        ->toContain(':where(.healthcare-shell p)')
        ->not->toContain('.healthcare-shell :where(')
        ->not->toMatch('/(?:^|\n)\s*\.healthcare-shell\s+(?:h1|h2|h3|p)\b/');
});

it('renders standard feature and content listing sections through healthcare registry fallbacks', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $html = $registry->renderer('healthcare')->render(new ThemePageData(
        title: 'Healthcare standard sections',
        brand: new BrandProfileData,
        sections: [
            new FeatureSectionData(
                heading: 'Service access',
                summary: 'Standard feature data renders through the healthcare services view.',
                features: [
                    ['title' => 'Rapid GP', 'description' => 'Same-week appointments.'],
                ],
            ),
            new ContentListingSectionData(
                heading: 'Patient resources',
                summary: 'Standard content listing data renders through the healthcare resource view.',
                items: [
                    ['title' => 'Referral checklist', 'summary' => 'Prepare for your first appointment.', 'url' => '/resources/referral-checklist', 'imageUrl' => '/images/referral.jpg'],
                ],
            ),
        ],
        navigation: new NavigationData(brandName: 'Aster Clinic'),
        footer: new FooterData(brandName: 'Aster Clinic'),
    ));

    expect($html)
        ->toContain('Service access')
        ->toContain('Rapid GP')
        ->toContain('Care pathway')
        ->toContain('Patient resources')
        ->toContain('Referral checklist')
        ->toContain('/images/referral.jpg')
        ->toContain('Clinically reviewed')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('renders new premium healthcare layouts through the registry', function (): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $carePathwayRenderer = $registry->sectionRenderer('healthcare', 'care-pathway');
    $locationsRenderer = $registry->sectionRenderer('healthcare', 'locations');
    $trustRenderer = $registry->sectionRenderer('healthcare', 'insurance-trust');

    assert($carePathwayRenderer instanceof SectionRenderer);
    assert($locationsRenderer instanceof SectionRenderer);
    assert($trustRenderer instanceof SectionRenderer);

    $carePathwayHtml = $carePathwayRenderer->render(healthcareThemeSection('care-pathway', [
        'heading' => 'Find the right care route',
        'items' => [
            ['title' => 'Same-week assessment', 'summary' => 'Route patients to the right appointment path.'],
        ],
    ]));

    $locationsHtml = $locationsRenderer->render(healthcareThemeSection('locations', [
        'heading' => 'Clinic access points',
        'items' => [
            ['title' => 'North clinic', 'summary' => 'Opening hours and contact routing.'],
        ],
    ]));

    $trustHtml = $trustRenderer->render(healthcareThemeSection('insurance-trust', [
        'heading' => 'Cover and trust signals',
        'items' => [
            ['title' => 'Recognised providers', 'summary' => 'Clear trust information for patient decisions.'],
        ],
    ]));

    expect($carePathwayHtml)
        ->toContain('Find the right care route')
        ->toContain('Same-week assessment')
        ->not->toContain('capell-app/theme-healthcare');

    expect($locationsHtml)
        ->toContain('Clinic access points')
        ->toContain('North clinic')
        ->not->toContain('capell-app/theme-healthcare');

    expect($trustHtml)
        ->toContain('Cover and trust signals')
        ->toContain('Recognised providers')
        ->not->toContain('capell-app/theme-healthcare');
});

it('passes optional Form Builder availability through the registered booking renderer', function (bool $formBuilderInstalled, string $expectedMarkup, string $missingMarkup): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/form-builder', $formBuilderInstalled);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('healthcare', 'booking');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(healthcareThemeSection('booking', [
        'heading' => 'Request an appointment',
        'items' => [['title' => 'Same-week triage']],
    ]));

    expect($html)
        ->toContain($expectedMarkup)
        ->not->toContain($missingMarkup)
        ->not->toContain('Form Builder')
        ->not->toContain('form-builder')
        ->not->toContain('capell-app/');
})->with([
    'form builder installed' => [true, 'Appointment request ready', 'Contact route ready'],
    'form builder not installed' => [false, 'Contact route ready', 'Appointment request ready'],
]);

it('passes optional Events availability through the registered events renderer', function (bool $eventsInstalled, string $expectedMarkup, string $missingMarkup): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/events', $eventsInstalled);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('healthcare', 'events');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(healthcareThemeSection('events', [
        'heading' => 'Care sessions',
        'items' => [['title' => 'Heart health evening', 'summary' => 'Consultant-led Q&A.', 'url' => '/events/heart-health']],
    ]));

    expect($html)
        ->toContain($expectedMarkup)
        ->not->toContain($missingMarkup);
})->with([
    'events installed' => [true, 'href="/events/heart-health"', 'grid gap-4 rounded-lg border border-[#d9e8ee] bg-[#f6fbfd] p-5 md:grid-cols-[8rem_1fr]"'],
    'events not installed' => [false, 'grid gap-4 rounded-lg border border-[#d9e8ee] bg-[#f6fbfd] p-5 md:grid-cols-[8rem_1fr]"', 'href="/events/heart-health"'],
]);

it('passes optional Blog availability through the registered blog teaser renderer', function (bool $blogInstalled, string $expectedMarkup, string $missingMarkup): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/blog', $blogInstalled);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('healthcare', 'blog-teaser');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(healthcareThemeSection('blog-teaser', [
        'heading' => 'Care resources',
        'items' => [
            ['title' => 'Preparing for a first consultation', 'summary' => 'What to bring and expect.', 'url' => '/resources/first-consultation'],
        ],
    ]));

    expect($html)
        ->toContain($expectedMarkup)
        ->not->toContain($missingMarkup);
})->with([
    'blog installed' => [true, 'href="/resources/first-consultation"', '<article'],
    'blog not installed' => [false, 'healthcare-resource-card', 'href="/resources/first-consultation"'],
]);

it('renders optional healthcare section views without database queries', function (): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/lang');

    $queries = [];

    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $bookingHtml = (new BookingSectionRenderer(HealthcareThemeServiceProvider::THEME_KEY, true, failLoudly: true))->render(
        healthcareThemeSection('booking', [
            'heading' => 'Request an appointment',
            'items' => [['title' => 'Same-week triage']],
        ]),
    );

    $eventsHtml = (new EventPanelSectionRenderer(HealthcareThemeServiceProvider::THEME_KEY, true, failLoudly: true))->render(
        healthcareThemeSection('events', [
            'heading' => 'Care sessions',
            'items' => [['title' => 'Heart health evening', 'summary' => 'Consultant-led Q&A.', 'url' => '/events/heart-health']],
        ]),
    );

    $blogHtml = (new BlogTeaserSectionRenderer(HealthcareThemeServiceProvider::THEME_KEY, 'blog-teaser', true, failLoudly: true))->render(
        healthcareThemeSection('blog-teaser', [
            'heading' => 'Care resources',
            'items' => [
                ['title' => 'Preparing for a first consultation', 'summary' => 'What to bring and expect.', 'url' => '/resources/first-consultation'],
            ],
        ]),
    );

    expect($bookingHtml)->toContain('Appointment request ready')
        ->and($eventsHtml)->toContain('Heart health evening')
        ->and($blogHtml)->toContain('Preparing for a first consultation')
        ->and($queries)->toBe([]);
});

/**
 * @param  array<string, mixed>  $viewData
 */
function healthcareThemeSection(string $key, array $viewData): ThemeSection
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
