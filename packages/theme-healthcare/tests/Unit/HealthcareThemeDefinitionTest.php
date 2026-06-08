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
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

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
            'clinician-profile',
            'conditions-directory',
            'booking',
            'emergency-escalation',
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
        ->and($definition->extends)->toBe('default')
        ->and($definition->tags)->toBe(['Healthcare', 'Appointments', 'Services'])
        ->and(data_get($manifest, 'product.tier'))->toBe('premium')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/foundation-theme')
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
        'clinician-profile',
        'conditions-directory',
        'booking',
        'emergency-escalation',
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
                actions: [['label' => 'Book appointment', 'url' => '/appointments']],
                mediaUrl: '/images/clinic-hero.jpg',
                mediaAlt: 'Clinician consultation room',
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
            healthcareThemeSection('clinician-profile', [
                'clinician' => [
                    'name' => 'Dr Amara Patel',
                    'summary' => 'Consultant physician focused on complex diagnostics.',
                    'credentials' => ['GMC registered', 'FRCP'],
                    'specialties' => ['Diagnostics', 'Cardiology'],
                    'languages' => ['English', 'Gujarati'],
                    'acceptingPatients' => true,
                ],
            ]),
            healthcareThemeSection('conditions-directory', [
                'heading' => 'Conditions and treatments',
                'items' => [
                    [
                        'title' => 'Chest pain assessment',
                        'summary' => 'Rapid triage routes for urgent symptoms.',
                        'services' => [['label' => 'Cardiology', 'url' => '/services/cardiology']],
                    ],
                ],
            ]),
            healthcareThemeSection('booking', [
                'heading' => 'Request an appointment',
                'items' => [['title' => 'Same-week triage']],
            ]),
            healthcareThemeSection('emergency-escalation', [
                'heading' => 'Urgent symptoms need urgent help',
                'summary' => 'Call emergency services for chest pain, stroke symptoms, or breathing difficulty.',
                'emergencyPhone' => '999',
                'urgentCareUrl' => '/urgent-care',
                'items' => [
                    ['title' => 'Call 999', 'summary' => 'Use emergency services for life-threatening symptoms.'],
                    ['title' => 'Use urgent care', 'summary' => 'Use clinic routes for non-emergency escalation.'],
                ],
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
                'items' => [['title' => 'Cardiff clinic', 'address' => 'Central Cardiff', 'phone' => '02920 000000', 'hours' => 'Mon-Fri 08:00-18:00', 'mapUrl' => 'https://maps.example/cardiff']],
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
        ->toContain('loading="eager"')
        ->toContain('loading="lazy"')
        ->toContain('width="1200"')
        ->toContain('height="900"')
        ->toContain('sizes="(min-width: 1024px) 48vw, 100vw"')
        ->toContain('width="800"')
        ->toContain('Previous items')
        ->toContain('Next items')
        ->toContain('GMC registered')
        ->toContain('Accepting new patients')
        ->toContain('Chest pain assessment')
        ->toContain('href="/services/cardiology"')
        ->toContain('href="tel:02920000000"')
        ->toContain('Mon-Fri 08:00-18:00')
        ->toContain('https://maps.example/cardiff')
        ->toContain('Urgent symptoms need urgent help')
        ->toContain('href="tel:999"')
        ->toContain('Use emergency services for life-threatening symptoms.')
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

it('routes healthcare section colours through theme tokens', function (): void {
    $views = healthcareThemeTokenBladeViews(
        __DIR__ . '/../../resources/views/sections',
        __DIR__ . '/../../resources/views/blog',
    );
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-healthcare.css') ?: '';

    expect($views)
        ->toContain('var(--healthcare-ink)')
        ->toContain('var(--healthcare-primary)')
        ->toContain('var(--healthcare-surface)')
        ->toContain('var(--healthcare-line)')
        ->not->toMatch('/#[0-9a-fA-F]{3,6}/');

    expect($css)
        ->toContain('--healthcare-primary-soft')
        ->toContain('--healthcare-primary-bright')
        ->toContain('--healthcare-ink-strong')
        ->toContain('--healthcare-link: var(--theme-link, #1d4ed8)')
        ->toContain('font-weight: 780')
        ->toContain('font-weight: 760');
});

it('ships dark-mode healthcare tokens through class and media strategies', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-healthcare.css') ?: '';

    expect($css)
        ->toContain(':where(.dark .healthcare-shell, .healthcare-shell.dark)')
        ->toContain('@media (prefers-color-scheme: dark)')
        ->toContain('--healthcare-ink: var(--theme-foreground-dark, #e5f3f2)')
        ->toContain('--healthcare-surface: var(--theme-surface-dark, #0f1f24)')
        ->toContain('--healthcare-primary: var(--theme-primary-dark, #5eead4)')
        ->toContain('--healthcare-accent: var(--theme-accent-dark, #fbbf24)')
        ->toContain('.bg-white')
        ->toContain('.text-stone-600')
        ->toContain('box-shadow: 0 24px 70px rgb(0 0 0 / 32%)');
});

it('keeps default healthcare token contrast at WCAG AA levels', function (): void {
    expect(healthcareContrastRatio('#14323a', '#f6fbfd'))->toBeGreaterThanOrEqual(9.0)
        ->and(healthcareContrastRatio('#425866', '#f6fbfd'))->toBeGreaterThanOrEqual(6.0)
        ->and(healthcareContrastRatio('#0f766e', '#ffffff'))->toBeGreaterThanOrEqual(4.5)
        ->and(healthcareContrastRatio('#1d4ed8', '#ffffff'))->toBeGreaterThanOrEqual(4.5)
        ->and(healthcareContrastRatio('#f59e0b', '#14323a'))->toBeGreaterThanOrEqual(4.5)
        ->and(healthcareContrastRatio('#ffffff', '#14323a'))->toBeGreaterThanOrEqual(9.0)
        ->and(healthcareContrastRatio('#e5f3f2', '#0f1f24'))->toBeGreaterThanOrEqual(9.0)
        ->and(healthcareContrastRatio('#b8c8cf', '#0f1f24'))->toBeGreaterThanOrEqual(6.0)
        ->and(healthcareContrastRatio('#5eead4', '#0f1f24'))->toBeGreaterThanOrEqual(7.0)
        ->and(healthcareContrastRatio('#fbbf24', '#0f1f24'))->toBeGreaterThanOrEqual(8.0)
        ->and(healthcareContrastRatio('#93c5fd', '#0f1f24'))->toBeGreaterThanOrEqual(7.0);
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

it('renders the healthcare emergency escalation section with safe urgent contact data', function (): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $renderer = $registry->sectionRenderer('healthcare', 'emergency-escalation');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(healthcareThemeSection('emergency-escalation', [
        'heading' => 'Urgent symptoms need urgent help',
        'summary' => 'If symptoms are severe, use emergency services before contacting the clinic.',
        'emergencyPhone' => '0300 123 456',
        'primaryAction' => ['label' => 'Read urgent-care guidance', 'url' => '/urgent-care'],
        'items' => [
            ['title' => 'Call emergency services', 'summary' => 'Use emergency routes for chest pain or stroke symptoms.'],
            ['title' => 'Contact urgent care', 'summary' => 'Use clinic routes for non-life-threatening escalation.'],
        ],
    ]));

    $emptyHtml = $renderer->render(healthcareThemeSection('emergency-escalation', [
        'heading' => null,
        'summary' => null,
        'items' => [],
    ]));

    expect($html)
        ->toContain('Urgent care notice')
        ->toContain('Urgent symptoms need urgent help')
        ->toContain('href="tel:0300123456"')
        ->toContain('Call 0300 123 456')
        ->toContain('Read urgent-care guidance')
        ->toContain('Use emergency routes for chest pain or stroke symptoms.')
        ->not->toContain('capell-app/theme-healthcare')
        ->not->toContain('theme-healthcare')
        ->not->toContain('model_id')
        ->not->toContain('field_path')
        ->and($emptyHtml)
        ->toContain('Know when to seek urgent help')
        ->toContain('Call emergency services')
        ->not->toContain('data-field')
        ->not->toContain('model_id');
});

it('renders healthcare clinician detail and conditions directory surfaces', function (): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/lang');
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $clinicianRenderer = $registry->sectionRenderer('healthcare', 'clinician-profile');
    $conditionsRenderer = $registry->sectionRenderer('healthcare', 'conditions-directory');

    expect($clinicianRenderer)->not->toBeNull()
        ->and($conditionsRenderer)->not->toBeNull();
    assert($clinicianRenderer instanceof SectionRenderer);
    assert($conditionsRenderer instanceof SectionRenderer);

    $clinicianHtml = $clinicianRenderer->render(healthcareThemeSection('clinician-profile', [
        'clinician' => [
            'name' => 'Dr Lena Morris',
            'bio' => 'Consultant dermatologist supporting complex skin pathways.',
            'image' => '/images/dr-lena.jpg',
            'imageAlt' => 'Dr Lena Morris',
            'credentials' => ['GMC registered', 'FRCP'],
            'specialties' => ['Dermatology', 'Skin cancer screening'],
            'languages' => ['English', 'Welsh'],
            'accepting_new_patients' => true,
        ],
    ]));

    $conditionsHtml = $conditionsRenderer->render(healthcareThemeSection('conditions-directory', [
        'heading' => 'Conditions and treatments',
        'summary' => 'Find the right clinical route.',
        'items' => [
            [
                'type' => 'Treatment',
                'title' => 'Mole assessment',
                'summary' => 'Rapid dermatology checks and onward care.',
                'services' => [
                    ['label' => 'Dermatology', 'url' => '/services/dermatology'],
                    ['label' => 'Skin screening', 'url' => '/services/skin-screening'],
                ],
            ],
        ],
    ]));

    $emptyConditionsHtml = $conditionsRenderer->render(healthcareThemeSection('conditions-directory', [
        'heading' => null,
        'summary' => null,
        'items' => [],
    ]));

    expect($clinicianHtml)
        ->toContain('Dr Lena Morris')
        ->toContain('Consultant dermatologist supporting complex skin pathways.')
        ->toContain('src="/images/dr-lena.jpg"')
        ->toContain('alt="Dr Lena Morris"')
        ->toContain('GMC registered')
        ->toContain('Skin cancer screening')
        ->toContain('English, Welsh')
        ->toContain('Accepting new patients')
        ->not->toContain('capell-app/theme-healthcare')
        ->not->toContain('theme-healthcare')
        ->not->toContain('model_id')
        ->not->toContain('field_path')
        ->and($conditionsHtml)
        ->toContain('Conditions and treatments')
        ->toContain('Find the right clinical route.')
        ->toContain('Mole assessment')
        ->toContain('href="/services/dermatology"')
        ->toContain('Skin screening')
        ->not->toContain('capell-app/theme-healthcare')
        ->not->toContain('theme-healthcare')
        ->not->toContain('model_id')
        ->not->toContain('field_path')
        ->and($emptyConditionsHtml)
        ->toContain('Add condition and treatment entries')
        ->toContain('Add conditions, treatments')
        ->not->toContain('data-field')
        ->not->toContain('model_id');
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
        'formHtml' => '<form action="/appointments" method="post"><button type="submit">Send request</button></form>',
        'phone' => '02920 000000',
    ]));

    expect($html)
        ->toContain($expectedMarkup)
        ->not->toContain($missingMarkup)
        ->not->toContain('Form Builder')
        ->not->toContain('form-builder')
        ->not->toContain('capell-app/');
})->with([
    'form builder installed' => [true, '<form action="/appointments" method="post">', 'Call the clinic'],
    'form builder not installed' => [false, 'Call the clinic', '<form action="/appointments" method="post">'],
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
        ->toContain('data-carousel="healthcare-events"')
        ->toContain('data-carousel-track')
        ->not->toContain('<script>')
        ->not->toContain($missingMarkup);
})->with([
    'events installed' => [true, 'href="/events/heart-health"', '<article'],
    'events not installed' => [false, '<article', 'href="/events/heart-health"'],
]);

it('renders healthcare item-driven empty states and location contact fields', function (): void {
    View::addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/views');
    resolve(Translator::class)->addNamespace('capell-theme-healthcare', __DIR__ . '/../../resources/lang');

    $serviceFinderHtml = view('capell-theme-healthcare::sections.service-finder', [
        'section' => (object) ['heading' => 'Find care', 'items' => []],
    ])->render();

    $servicesHtml = view('capell-theme-healthcare::sections.services', [
        'section' => (object) ['heading' => 'Services', 'items' => []],
    ])->render();

    $cliniciansHtml = view('capell-theme-healthcare::sections.clinicians', [
        'section' => (object) ['heading' => 'Clinicians', 'items' => []],
    ])->render();

    $contactHtml = view('capell-theme-healthcare::sections.contact', [
        'section' => (object) ['heading' => 'Contact', 'items' => []],
    ])->render();

    $locationsHtml = view('capell-theme-healthcare::sections.locations', [
        'section' => (object) [
            'heading' => 'Locations',
            'items' => [[
                'title' => 'North clinic',
                'address' => '12 High Street',
                'hours' => 'Mon-Fri 08:00-18:00',
                'phone' => '02920 000000',
                'mapUrl' => 'https://maps.example/north',
            ]],
        ],
    ])->render();

    expect($serviceFinderHtml)
        ->toContain('Service finder filters are ready for patient pathway planning.')
        ->and($servicesHtml)->toContain('Clinical service cards are ready for publication.')
        ->and($cliniciansHtml)->toContain('Clinician profiles are ready for publication.')
        ->and($contactHtml)->toContain('Clinic access details are ready for contact and visit planning.')
        ->and($locationsHtml)->toContain('12 High Street')
        ->and($locationsHtml)->toContain('Mon-Fri 08:00-18:00')
        ->and($locationsHtml)->toContain('href="tel:02920000000"')
        ->and($locationsHtml)->toContain('https://maps.example/north');
});

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

it('renders the healthcare page inside the declared frontend budget without database queries', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $queryCount = 0;

    DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
        $queryCount++;
    });

    $registry = new ThemeRegistry;
    $provider = new HealthcareThemeServiceProvider($this->app);
    $provider->boot($registry);

    $queryCount = 0;
    $startedAt = hrtime(true);

    $html = $registry->renderer('healthcare')->render(new ThemePageData(
        title: 'Healthcare budget render',
        brand: new BrandProfileData,
        sections: [
            new HeroSectionData(
                heading: 'Specialist care with clear next steps',
                summary: 'Appointment-led service discovery.',
            ),
            healthcareThemeSection('services', [
                'heading' => 'Clinical services',
                'items' => [['title' => 'Rapid GP', 'summary' => 'Same-week appointments.']],
            ]),
            healthcareThemeSection('booking', [
                'heading' => 'Request an appointment',
                'items' => [['title' => 'Triage call']],
            ]),
            healthcareThemeSection('emergency-escalation', [
                'heading' => 'Urgent symptoms need urgent help',
                'items' => [['title' => 'Call emergency services']],
            ]),
            new ProofSectionData(
                heading: 'Trusted by patients',
                items: [['metric' => '98%', 'name' => 'Patient satisfaction']],
            ),
            new CtaSectionData(
                heading: 'Start with the right appointment',
                actions: [['label' => 'Book appointment', 'url' => '/appointments']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Aster Clinic',
            items: [['label' => 'Services', 'url' => '/services']],
        ),
        footer: new FooterData(brandName: 'Aster Clinic'),
    ));

    $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;

    expect($elapsedMilliseconds)->toBeLessThanOrEqual(healthcareFrontendRenderBudgetMs($manifest))
        ->and($queryCount)->toBe(0)
        ->and($html)->toContain('Specialist care with clear next steps')
        ->and($html)->toContain('Urgent symptoms need urgent help')
        ->and($html)->not->toContain('capell-app/theme-healthcare');
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

function healthcareThemeTokenBladeViews(string ...$directories): string
{
    $views = [];

    foreach ($directories as $directory) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }

            if (! $file->isFile()) {
                continue;
            }

            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $views[] = file_get_contents($file->getPathname()) ?: '';
        }
    }

    return implode("\n", $views);
}

/**
 * @param  array<string, mixed>  $manifest
 */
function healthcareFrontendRenderBudgetMs(array $manifest): float
{
    $budget = data_get($manifest, 'performance.frontendRenderBudgetMs', 20);

    return is_numeric($budget) ? (float) $budget : 20.0;
}

function healthcareContrastRatio(string $foreground, string $background): float
{
    $foregroundLuminance = healthcareRelativeLuminance($foreground);
    $backgroundLuminance = healthcareRelativeLuminance($background);

    $lighter = max($foregroundLuminance, $backgroundLuminance);
    $darker = min($foregroundLuminance, $backgroundLuminance);

    return ($lighter + 0.05) / ($darker + 0.05);
}

function healthcareRelativeLuminance(string $hex): float
{
    $hex = ltrim($hex, '#');

    $channels = [
        hexdec(substr($hex, 0, 2)) / 255,
        hexdec(substr($hex, 2, 2)) / 255,
        hexdec(substr($hex, 4, 2)) / 255,
    ];

    [$red, $green, $blue] = array_map(
        static fn (float $channel): float => $channel <= 0.03928
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4,
        $channels,
    );

    return (0.2126 * $red) + (0.7152 * $green) + (0.0722 * $blue);
}
