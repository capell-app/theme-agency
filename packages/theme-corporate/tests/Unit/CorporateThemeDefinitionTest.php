<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
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
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Corporate\CorporateThemeServiceProvider;
use Capell\ThemeStudio\Corporate\Health\ThemeCorporateHealthCheck;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

it('defines the corporate free renderer contract', function (): void {
    $definition = CorporateThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-corporate')
        ->and($definition->key)->toBe(CorporateThemeServiceProvider::THEME_KEY)
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/corporate.css'])
        ->and($definition->includedSections)->toContain('hero', 'features', 'proof', 'locations', 'investor-relations', 'careers', 'cta')
        ->and($definition->presets)->toHaveCount(6)
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->tags)->toContain('Trust')
        ->and(ThemeCorporateHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('renders navigation from the corporate package views', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');

    $provider = new CorporateThemeServiceProvider($this->app);
    $renderer = corporateThemeRenderer($provider, 'navigation');

    $html = $renderer->render(new NavigationData(
        brandName: 'Capell',
        items: [['label' => 'Home', 'url' => '/']],
    ));

    expect($html)
        ->toContain('Capell')
        ->toContain('Home')
        ->toContain('data-corporate-menu')
        ->toContain('aria-expanded="false"');
});

it('declares renderers for every included corporate section', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');

    $provider = new CorporateThemeServiceProvider($this->app);
    $renderers = corporateThemeSectionRenderers($provider);

    expect(array_keys($renderers))->toBe([
        'navigation',
        'hero',
        'features',
        'proof',
        'content-listing',
        'locations',
        'investor-relations',
        'careers',
        'cta',
        'footer',
    ]);
});

it('renders the corporate hero with a board briefing fallback', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Governance for growing teams',
            eyebrow: 'Advisory',
            summary: 'Practical strategy, compliance, and delivery support.',
            actions: [['label' => 'Explore services', 'url' => '/services']],
        ),
    ])->render();

    expect($html)
        ->toContain('Governance for growing teams')
        ->toContain('Board briefing')
        ->toContain('Board pack')
        ->toContain('Pack ready')
        ->toContain('Decision log')
        ->toContain('Agenda')
        ->not->toContain('capell-app/theme-corporate');
});

it('renders corporate hero media with LCP image attributes', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Governance for growing teams',
            summary: 'Practical strategy, compliance, and delivery support.',
            mediaUrl: '/images/corporate-hero.jpg',
            mediaAlt: 'Board briefing dashboard',
        ),
    ])->render();

    expect($html)
        ->toContain('src="/images/corporate-hero.jpg"')
        ->toContain('alt="Board briefing dashboard"')
        ->toContain('width="1200"')
        ->toContain('height="900"')
        ->toContain('loading="eager"')
        ->toContain('decoding="async"')
        ->toContain('fetchpriority="high"')
        ->toContain('sizes="(min-width: 1024px) 52vw, 100vw"');
});

it('renders translated fallback and custom corporate hero stats', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $fallbackHtml = view('capell-theme-corporate::sections.hero', [
        'section' => new HeroSectionData(
            heading: 'Governance for growing teams',
            summary: 'Practical strategy, compliance, and delivery support.',
        ),
    ])->render();

    expect($fallbackHtml)
        ->toContain('Confidence')
        ->toContain('Clear signals')
        ->toContain('Board support')
        ->toContain('Structured proof')
        ->not->toContain('Pages')
        ->not->toContain('Content')
        ->not->toContain('Media')
        ->not->toContain('Assets')
        ->not->toContain('Layout')
        ->not->toContain('Widgets');

    $customHtml = view('capell-theme-corporate::sections.hero', [
        'section' => (object) [
            'heading' => 'Governance for growing teams',
            'eyebrow' => null,
            'summary' => null,
            'actions' => [],
            'mediaAlt' => null,
            'mediaUrl' => null,
            'stats' => [
                ['label' => 'Offices', 'value' => '12'],
                ['label' => 'Clients', 'value' => '240'],
                ['label' => 'Markets', 'value' => '8'],
            ],
        ],
    ])->render();

    expect($customHtml)
        ->toContain('Offices')
        ->toContain('240')
        ->toContain('Markets')
        ->not->toContain('Clear signals');
});

it('drives the corporate shell and card surfaces from theme tokens', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');

    $pageHtml = view('capell-theme-corporate::page', [
        'brand' => new BrandProfileData(
            surfaceColor: '#fbfaf7',
            foregroundColor: '#18201f',
        ),
        'content' => '<main id="main-content">Preview</main>',
    ])->render();

    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-corporate.css') ?: '';
    $views = implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        glob(__DIR__ . '/../../resources/views/sections/*.blade.php') ?: [],
    ));

    expect($pageHtml)
        ->toContain('--theme-surface:#fbfaf7')
        ->toContain('--theme-foreground:#18201f')
        ->toContain('class="site-theme-shell min-h-screen antialiased"')
        ->not->toContain('bg-[#f7f8f6]')
        ->not->toContain('text-slate-950 antialiased');

    expect($css)
        ->toContain('--corporate-surface: var(--theme-surface, #f7f8f6)')
        ->toContain('--corporate-card-radius: var(--theme-radius-value, 0.35rem)')
        ->toContain('--corporate-card-padding: var(--theme-card-padding, 1.25rem)')
        ->toContain('.corporate-card')
        ->toContain('.corporate-card-muted')
        ->toContain('.corporate-card-elevated');

    expect($views)
        ->toContain('corporate-surface')
        ->toContain('corporate-card')
        ->toContain('corporate-card-muted')
        ->toContain('corporate-card-elevated')
        ->not->toContain('bg-[#f7f8f6]');
});

it('renders translated corporate proof aria labels with generic public selectors', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.proof', [
        'section' => new ProofSectionData(
            heading: 'Evidence',
            items: [
                [
                    'image' => '/proof.jpg',
                    'name' => 'Assurance review',
                    'quote' => 'Approvals moved faster.',
                ],
            ],
        ),
    ])->render();

    expect($html)
        ->toContain('aria-label="Open image for Assurance review"')
        ->toContain('aria-label="Previous proof cards"')
        ->toContain('aria-label="Next proof cards"')
        ->toContain('aria-label="Close gallery"')
        ->toContain('aria-live="polite"')
        ->toContain('data-carousel-status')
        ->toContain('aria-disabled="true"')
        ->toContain('More proof cards are available.')
        ->toContain('Proof cards are visible.')
        ->toContain('data-carousel="proof"')
        ->not->toContain('data-carousel="corporate-proof"')
        ->not->toContain('‹')
        ->not->toContain('›');
});

it('ships accessible reduced-motion corporate carousel and menu behavior', function (): void {
    $script = file_get_contents(__DIR__ . '/../../resources/js/theme-corporate.js') ?: '';

    expect($script)
        ->toContain("querySelectorAll('[data-corporate-menu]')")
        ->toContain("querySelectorAll('[data-carousel=\"proof\"]')")
        ->toContain('prefers-reduced-motion: reduce')
        ->toContain('aria-expanded')
        ->toContain('aria-disabled')
        ->toContain('data-carousel-status')
        ->toContain('scrollBy');
});

it('renders content listing variant labels from translations', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.content-listing', [
        'section' => new ContentListingSectionData(
            heading: 'Common questions',
            items: [
                [
                    'title' => 'How do we start?',
                    'summary' => 'Start with a readiness review.',
                    'url' => '/questions/start',
                ],
            ],
            variant: 'faq',
        ),
    ])->render();

    expect($html)
        ->toContain('FAQ')
        ->toContain('Question 1')
        ->not->toContain('{{ ucfirst($variant) }}')
        ->not->toContain('Question {{ $loop->iteration }}');
});

it('renders a corporate office locations section', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $provider = new CorporateThemeServiceProvider($this->app);
    $renderer = corporateThemeRenderer($provider, 'locations');

    $html = $renderer->render(corporateThemeSection('locations', [
        'heading' => 'Global advisory offices',
        'summary' => 'Regional teams for board, policy, and delivery support.',
        'items' => [
            [
                'type' => 'Head office',
                'title' => 'London',
                'summary' => 'Executive advisory and investor relations.',
                'addressLines' => ['10 King Street', 'London SW1A 1AA'],
                'phone' => '+44 20 7946 0100',
                'email' => 'london@example.test',
                'hours' => ['Mon-Fri 09:00-17:30'],
            ],
            [
                'type' => 'Regional office',
                'title' => 'Manchester',
                'address' => "2 Bridge Street\nManchester M1 1AA",
                'openingHours' => "Mon-Thu 09:00-17:00\nFri 09:00-16:00",
            ],
        ],
    ]));

    expect($html)
        ->toContain('Global advisory offices')
        ->toContain('Regional teams for board, policy, and delivery support.')
        ->toContain('London')
        ->toContain('10 King Street')
        ->toContain('+44 20 7946 0100')
        ->toContain('london@example.test')
        ->toContain('Manchester M1 1AA')
        ->toContain('Fri 09:00-16:00')
        ->toContain('corporate-card-muted')
        ->not->toContain('capell-app/theme-corporate')
        ->not->toContain('capell-theme-corporate');
});

it('renders the corporate office empty state without authoring metadata', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.locations', [
        'section' => (object) [
            'heading' => 'Corporate offices',
            'summary' => null,
            'items' => [],
        ],
    ])->render();

    expect($html)
        ->toContain('Add office details')
        ->toContain('Provide regional offices')
        ->not->toContain('data-field')
        ->not->toContain('model_id')
        ->not->toContain('capell-app/theme-corporate');
});

it('renders corporate investor relations and careers patterns', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $provider = new CorporateThemeServiceProvider($this->app);
    $renderers = corporateThemeSectionRenderers($provider);

    expect($renderers)->toHaveKeys(['investor-relations', 'careers']);

    $investorHtml = $renderers['investor-relations']->render(corporateThemeSection('investor-relations', [
        'heading' => 'Investor centre',
        'summary' => 'Regulated updates and financial reporting.',
        'items' => [
            [
                'period' => 'FY26',
                'title' => 'Annual report',
                'summary' => 'Audited results and governance statement.',
                'type' => 'Report',
                'url' => '/investors/annual-report',
            ],
        ],
        'events' => [
            ['title' => 'Capital markets day', 'date' => '12 September 2026'],
        ],
        'documents' => [
            ['title' => 'Trading update', 'url' => '/investors/trading-update'],
        ],
    ]));

    $careersHtml = $renderers['careers']->render(corporateThemeSection('careers', [
        'heading' => 'Open roles',
        'summary' => 'Join a board advisory team.',
        'benefits' => ['Hybrid working', 'Learning budget'],
        'items' => [
            [
                'team' => 'Advisory',
                'title' => 'Governance consultant',
                'summary' => 'Support client boards with structured reporting.',
                'location' => 'London / hybrid',
                'type' => 'Full time',
                'url' => '/careers/governance-consultant',
                'ctaLabel' => 'Apply now',
            ],
        ],
    ]));

    expect($investorHtml)
        ->toContain('Investor centre')
        ->toContain('Regulated updates and financial reporting.')
        ->toContain('FY26')
        ->toContain('Annual report')
        ->toContain('Capital markets day')
        ->toContain('Trading update')
        ->not->toContain('capell-app/theme-corporate')
        ->not->toContain('capell-theme-corporate')
        ->not->toContain('model_id')
        ->not->toContain('field_path')
        ->and($careersHtml)
        ->toContain('Open roles')
        ->toContain('Join a board advisory team.')
        ->toContain('Hybrid working')
        ->toContain('Governance consultant')
        ->toContain('London / hybrid')
        ->toContain('Full time')
        ->toContain('Apply now')
        ->not->toContain('capell-app/theme-corporate')
        ->not->toContain('capell-theme-corporate')
        ->not->toContain('model_id')
        ->not->toContain('field_path');
});

it('renders corporate investor and careers empty states without authoring metadata', function (): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $investorHtml = view('capell-theme-corporate::sections.investor-relations', [
        'section' => (object) [
            'heading' => null,
            'summary' => null,
            'items' => [],
            'events' => [],
            'documents' => [],
        ],
    ])->render();

    $careersHtml = view('capell-theme-corporate::sections.careers', [
        'section' => (object) [
            'heading' => null,
            'summary' => null,
            'items' => [],
            'benefits' => [],
        ],
    ])->render();

    expect($investorHtml)
        ->toContain('Add investor updates')
        ->toContain('Add annual reports')
        ->not->toContain('data-field')
        ->not->toContain('model_id')
        ->not->toContain('capell-app/theme-corporate')
        ->and($careersHtml)
        ->toContain('Add open roles')
        ->toContain('Add open roles, locations')
        ->not->toContain('data-field')
        ->not->toContain('model_id')
        ->not->toContain('capell-app/theme-corporate');
});

it('registers corporate only when the theme package is installed', function (): void {
    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    $provider = new CorporateThemeServiceProvider($this->app);
    $provider->register();
    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName, false);
    $provider->boot($registry);

    expect($registry->has('corporate'))->toBeFalse();

    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName);

    $provider->boot($registry);

    expect($registry->has('corporate'))->toBeTrue()
        ->and($registry->definition('corporate')->package)->toBe(CorporateThemeServiceProvider::$packageName);
});

it('registers corporate tailwind imports and blade sources when installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CorporateThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CorporateThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CorporateThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageBuildAssets = CapellCore::getVendorAssetsForType(VendorAssetEnum::BuildAsset)
        ->filter(fn (mixed $asset): bool => $asset->packageName === CorporateThemeServiceProvider::$packageName)
        ->map(fn (mixed $asset): string => $asset->path() . '/' . $asset->file())
        ->all();

    expect($packageImports)->toContain('resources/css/theme-corporate.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php')
        ->and($packageBuildAssets)->toContain('vendor/capell-theme-corporate/resources/js/theme-corporate.js');
});

it('renders public theme markup without package identifiers', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new CorporateThemeServiceProvider($this->app);
    $provider->register();
    $provider->boot($registry);

    $html = $registry->renderer('corporate')->render(new ThemePageData(
        title: 'Advisory',
        brand: new BrandProfileData,
        sections: [
            new HeroSectionData(
                heading: 'Governance for growing teams',
                eyebrow: 'Advisory',
                summary: 'Practical strategy, compliance, and delivery support.',
                actions: [['label' => 'Explore services', 'url' => '/services']],
            ),
            new FeatureSectionData(
                heading: 'Trusted operating support',
                features: [['title' => 'Risk reviews', 'description' => 'Structured reviews for critical decisions.']],
            ),
            new ProofSectionData(
                heading: 'Evidence',
                items: [['metric' => '24%', 'name' => 'Faster approvals']],
            ),
            new ContentListingSectionData(
                heading: 'Insights',
                items: [['title' => 'Board reporting', 'summary' => 'A clearer monthly reporting model.', 'url' => '/insights/board-reporting']],
            ),
            new CtaSectionData(
                heading: 'Talk to an advisor',
                actions: [['label' => 'Book a call', 'url' => '/contact']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Northbridge Advisory',
            items: [['label' => 'Services', 'url' => '/services']],
            ctaLabel: 'Contact',
            ctaUrl: '/contact',
        ),
        footer: new FooterData(
            brandName: 'Northbridge Advisory',
            columns: [
                ['heading' => 'Company', 'links' => [['label' => 'Contact', 'url' => '/contact']]],
            ],
        ),
    ));

    expect($html)
        ->toContain('Northbridge Advisory')
        ->toContain('Operating model')
        ->toContain('Assurance')
        ->toContain('Board action')
        ->toContain('Register')
        ->not->toContain('data-capell-theme')
        ->not->toContain('capell-theme')
        ->not->toContain('capell-app/theme-corporate')
        ->not->toContain('capell-theme-corporate')
        ->not->toContain('signed')
        ->not->toContain('filament')
        ->not->toContain('editor');
});

it('renders the uncached corporate page inside the declared frontend budget without database queries', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName);

    $manifest = corporateThemeManifest();
    $queryCount = 0;

    DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
        $queryCount++;
    });

    $registry = new ThemeRegistry;
    $provider = new CorporateThemeServiceProvider($this->app);
    $provider->register();
    $provider->boot($registry);

    $queryCount = 0;
    $startedAt = hrtime(true);

    $html = $registry->renderer('corporate')->render(new ThemePageData(
        title: 'Corporate budget render',
        brand: new BrandProfileData,
        sections: [
            new HeroSectionData(
                heading: 'Governance for growing teams',
                summary: 'Practical strategy, compliance, and delivery support.',
            ),
            new FeatureSectionData(
                heading: 'Operating model',
                features: [
                    ['title' => 'Risk review', 'description' => 'Structured review cadence.'],
                    ['title' => 'Policy pack', 'description' => 'Board-ready operating documents.'],
                ],
            ),
            new ProofSectionData(
                heading: 'Evidence',
                items: [
                    ['metric' => '24%', 'name' => 'Faster approvals', 'role' => 'Approval cycles shortened.'],
                    ['metric' => '12', 'name' => 'Regional boards', 'role' => 'Governance teams aligned.'],
                ],
            ),
            new ContentListingSectionData(
                heading: 'Briefings',
                items: [['title' => 'Board reporting', 'summary' => 'Monthly reporting model.', 'url' => '/briefings/reporting']],
            ),
            new CtaSectionData(
                heading: 'Talk to an advisor',
                actions: [['label' => 'Book a call', 'url' => '/contact']],
            ),
        ],
        navigation: new NavigationData(
            brandName: 'Northbridge Advisory',
            items: [['label' => 'Services', 'url' => '/services']],
            ctaLabel: 'Contact',
            ctaUrl: '/contact',
        ),
        footer: new FooterData(
            brandName: 'Northbridge Advisory',
            columns: [
                ['heading' => 'Company', 'links' => [['label' => 'Contact', 'url' => '/contact']]],
            ],
        ),
    ));

    $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;

    expect($elapsedMilliseconds)->toBeLessThanOrEqual(corporateThemeFrontendRenderBudgetMs($manifest))
        ->and($queryCount)->toBe(0)
        ->and($html)->toContain('Governance for growing teams')
        ->and($html)->toContain('Northbridge Advisory')
        ->and($html)->not->toContain('capell-app/theme-corporate');
});

it('renders the corporate content listing variant matrix', function (string $variant, string $expectedMarkup): void {
    View::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/views');
    View::addNamespace('capell', __DIR__ . '/../../../../../capell-4/packages/frontend/resources/views');
    Lang::addNamespace('capell-theme-corporate', __DIR__ . '/../../resources/lang');

    $html = view('capell-theme-corporate::sections.content-listing', [
        'section' => new ContentListingSectionData(
            heading: 'Corporate resources',
            summary: 'Structured updates for stakeholders.',
            items: [
                [
                    'title' => 'Board reporting model',
                    'summary' => 'A clearer monthly reporting model.',
                    'url' => '/insights/board-reporting',
                    'image' => '/images/reporting.jpg',
                    'type' => 'Briefing',
                    'meta' => ['Governance'],
                ],
            ],
            variant: $variant,
        ),
    ])->render();

    expect($html)
        ->toContain('Corporate resources')
        ->toContain($expectedMarkup)
        ->not->toContain('capell-app/theme-corporate');
})->with([
    'editorial' => ['editorial', 'Register'],
    'media' => ['media', 'Media'],
    'faq' => ['faq', 'Question 1'],
    'people' => ['people', 'Governance'],
    'metrics' => ['metrics', 'Metrics'],
    'gallery fallback' => ['gallery', 'Board reporting model'],
    'pathways fallback' => ['pathways', 'Board reporting model'],
    'spotlight fallback' => ['spotlight', 'Board reporting model'],
]);

/**
 * @param  array<string, mixed>  $viewData
 */
function corporateThemeSection(string $key, array $viewData): ThemeSection
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
 * @return array<string, ViewSectionRenderer>
 */
function corporateThemeSectionRenderers(CorporateThemeServiceProvider $provider): array
{
    $method = new ReflectionMethod($provider, 'sectionRenderers');
    $renderers = $method->invoke($provider);

    if (! is_array($renderers)) {
        return [];
    }

    $typedRenderers = [];

    foreach ($renderers as $key => $renderer) {
        if (is_string($key) && $renderer instanceof ViewSectionRenderer) {
            $typedRenderers[$key] = $renderer;
        }
    }

    return $typedRenderers;
}

function corporateThemeRenderer(CorporateThemeServiceProvider $provider, string $key): ViewSectionRenderer
{
    $renderer = corporateThemeSectionRenderers($provider)[$key] ?? null;

    throw_unless($renderer instanceof ViewSectionRenderer, RuntimeException::class);

    return $renderer;
}

/**
 * @param  array<string, mixed>  $manifest
 */
function corporateThemeFrontendRenderBudgetMs(array $manifest): float
{
    $budget = data_get($manifest, 'performance.frontendRenderBudgetMs', 20);

    return is_numeric($budget) ? (float) $budget : 20.0;
}

/**
 * @return array<string, mixed>
 */
function corporateThemeManifest(): array
{
    $manifest = json_decode((string) file_get_contents(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($manifest)) {
        throw new RuntimeException('Theme manifest must decode to an array.');
    }

    $stringKeyedManifest = [];

    foreach ($manifest as $key => $value) {
        if (is_string($key)) {
            $stringKeyedManifest[$key] = $value;
        }
    }

    return $stringKeyedManifest;
}
