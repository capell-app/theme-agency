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
use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(PackagesTestCase::class);

it('defines the Nonprofit theme contract', function (): void {
    $definition = NonprofitThemeServiceProvider::definition();

    expect($definition->key)->toBe('nonprofit')
        ->and($definition->package)->toBe('capell-app/theme-nonprofit')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('content-listing')
        ->and($definition->includedSections)->toContain('newsletter')
        ->and($definition->includedSections)->toContain('cta')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('renders standard sections through Nonprofit views', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $featureRenderer = $registry->sectionRenderer('nonprofit', 'features');
    $listingRenderer = $registry->sectionRenderer('nonprofit', 'content-listing');
    $ctaRenderer = $registry->sectionRenderer('nonprofit', 'cta');
    $proofRenderer = $registry->sectionRenderer('nonprofit', 'proof');

    assert($featureRenderer instanceof SectionRenderer);
    assert($listingRenderer instanceof SectionRenderer);
    assert($ctaRenderer instanceof SectionRenderer);
    assert($proofRenderer instanceof SectionRenderer);

    $featureHtml = $featureRenderer->render(new FeatureSectionData(
        heading: 'Impact pathways',
        summary: 'Supporter cards should feel specific to nonprofit work.',
        features: [
            ['title' => 'Campaign paths', 'description' => 'Move supporters from belief to action.', 'type' => 'Campaigns'],
        ],
    ));

    $listingHtml = $listingRenderer->render(new ContentListingSectionData(
        heading: 'Community stories',
        summary: 'Cards should support campaigns and proof.',
        items: [
            ['title' => 'Neighbourhood appeal', 'summary' => 'Show a campaign outcome.', 'type' => 'Appeal'],
        ],
    ));

    $ctaHtml = $ctaRenderer->render(new CtaSectionData(
        heading: 'Back the next campaign',
        summary: 'Move supporters into action.',
        actions: [['label' => 'Donate now', 'url' => '#donate', 'style' => 'primary']],
    ));

    $proofHtml = $proofRenderer->render(new ProofSectionData(
        heading: 'Campaign outcomes',
        summary: 'Proof should feel like supporter and campaign evidence.',
        items: [
            ['metric' => '84%', 'name' => 'Funded', 'summary' => 'Supporters moved the appeal toward its next milestone.'],
        ],
    ));

    expect($featureHtml)
        ->toContain('Impact pathways')
        ->toContain('Impact paths')
        ->toContain('Donor ready')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($listingHtml)
        ->toContain('Community stories')
        ->toContain('Neighbourhood appeal')
        ->toContain('Supporter ready')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($ctaHtml)
        ->toContain('Back the next campaign')
        ->toContain('Supporter action')
        ->toContain('Donate now')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($proofHtml)
        ->toContain('Supporter proof')
        ->toContain('Campaign proof')
        ->toContain('84%')
        ->toContain('Funded')
        ->not->toContain('capell-app/theme-nonprofit');
});

it('renders hydrated hero data through the Nonprofit hero view', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('nonprofit', 'hero');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(nonprofitThemeSection('hero', [
        'heading' => 'Fund the next community appeal',
        'summary' => 'Hydrated nonprofit hero summary.',
        'mediaUrl' => 'https://example.test/nonprofit-hero.jpg',
        'mediaAlt' => 'Volunteers sorting winter support supplies',
        'campaignTitle' => 'Emergency food fund',
        'campaignValue' => '63%',
        'campaignProgress' => 63,
        'actions' => [
            ['label' => 'Donate today', 'url' => '#donate'],
            ['label' => 'Join the team', 'url' => '#volunteer'],
        ],
    ]));

    expect($html)
        ->toContain('Campaign command centre')
        ->toContain('<h1')
        ->toContain('Fund the next community appeal')
        ->toContain('Hydrated nonprofit hero summary.')
        ->toContain('Donate today')
        ->toContain('Join the team')
        ->toContain('https://example.test/nonprofit-hero.jpg')
        ->toContain('alt="Volunteers sorting winter support supplies"')
        ->toContain('width="1200"')
        ->toContain('height="675"')
        ->toContain('loading="eager"')
        ->toContain('fetchpriority="high"')
        ->toContain('Emergency food fund')
        ->toContain('63%')
        ->toContain('role="progressbar"')
        ->toContain('aria-valuenow="63"')
        ->toContain('style="width: 63%"')
        ->not->toContain('capell-app/theme-nonprofit');
});

it('renders one public h1 after the skip-link target while sections keep h2 headings', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $heroRenderer = $registry->sectionRenderer('nonprofit', 'hero');
    $featureRenderer = $registry->sectionRenderer('nonprofit', 'features');

    assert($heroRenderer instanceof SectionRenderer);
    assert($featureRenderer instanceof SectionRenderer);

    $heroHtml = $heroRenderer->render(HeroSectionData::from([
        'heading' => 'Fund the next community appeal',
        'summary' => 'Hydrated nonprofit hero summary.',
    ]));

    $featureHtml = $featureRenderer->render(new FeatureSectionData(
        heading: 'Impact pathways',
        summary: 'Supporter cards should feel specific to nonprofit work.',
        features: [
            ['title' => 'Campaign paths', 'description' => 'Move supporters from belief to action.', 'type' => 'Campaigns'],
        ],
    ));

    $page = view('capell-theme-nonprofit::page', [
        'brand' => new readonly class
        {
            /**
             * @return array<string, string>
             */
            public function tokens(): array
            {
                return ['--theme-primary' => '#166534'];
            }
        },
        'content' => $heroHtml . $featureHtml,
    ])->render();

    $mainContentPosition = strpos($page, 'id="main-content"');
    $headingPosition = strpos($page, '<h1');

    expect(substr_count($page, '<h1'))->toBe(1)
        ->and($mainContentPosition)->not->toBeFalse()
        ->and($headingPosition)->not->toBeFalse();

    throw_unless(is_int($mainContentPosition) && is_int($headingPosition), RuntimeException::class, 'Expected heading and main content positions.');

    expect($headingPosition)->toBeGreaterThan($mainContentPosition)
        ->and($page)->toContain('id="main-content"')
        ->and($page)->toContain('<h1')
        ->and($page)->toContain('Fund the next community appeal')
        ->and($page)->toContain('<h2')
        ->and($page)->toContain('Impact pathways')
        ->and($page)->not->toContain('capell-app/theme-nonprofit');
});

it('renders new premium nonprofit layouts through the registry', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $donationImpactRenderer = $registry->sectionRenderer('nonprofit', 'donation-impact');
    $volunteerShiftsRenderer = $registry->sectionRenderer('nonprofit', 'volunteer-shifts');
    $annualReportRenderer = $registry->sectionRenderer('nonprofit', 'annual-report-proof');

    assert($donationImpactRenderer instanceof SectionRenderer);
    assert($volunteerShiftsRenderer instanceof SectionRenderer);
    assert($annualReportRenderer instanceof SectionRenderer);

    $donationImpactHtml = $donationImpactRenderer->render(nonprofitThemeSection('donation-impact', [
        'heading' => 'Fund measurable impact',
        'items' => [
            ['title' => 'Meals for a week', 'summary' => 'Donation ladder content with concrete outcomes.'],
        ],
    ]));

    $volunteerShiftsHtml = $volunteerShiftsRenderer->render(nonprofitThemeSection('volunteer-shifts', [
        'heading' => 'Join a practical shift',
        'items' => [
            ['title' => 'Saturday outreach', 'summary' => 'Volunteer roles grouped around real community work.'],
        ],
    ]));

    $annualReportHtml = $annualReportRenderer->render(nonprofitThemeSection('annual-report-proof', [
        'heading' => 'Report the outcomes',
        'items' => [
            ['title' => 'Transparent spend', 'summary' => 'Annual proof for supporter confidence.'],
        ],
    ]));

    expect($donationImpactHtml)
        ->toContain('Fund measurable impact')
        ->toContain('Meals for a week')
        ->not->toContain('Donate now')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($volunteerShiftsHtml)
        ->toContain('Join a practical shift')
        ->toContain('Saturday outreach')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($annualReportHtml)
        ->toContain('Report the outcomes')
        ->toContain('Transparent spend')
        ->not->toContain('capell-app/theme-nonprofit');
});

it('renders Payments-aware donation actions from hydrated section data', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/payments');

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('nonprofit', 'donation-impact');

    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(nonprofitThemeSection('donation-impact', [
        'heading' => 'Give where it matters',
        'primaryAction' => ['label' => 'Give monthly', 'url' => '/give/monthly'],
        'secondaryAction' => ['label' => 'View outcomes', 'url' => '/impact'],
    ]));

    expect($html)
        ->toContain('Give where it matters')
        ->toContain('Payments-powered giving routes')
        ->toContain('href="/give/monthly"')
        ->toContain('Give monthly')
        ->toContain('href="/impact"')
        ->toContain('View outcomes')
        ->not->toContain('capell-app/theme-nonprofit');
});

it('renders Newsletter-aware supporter capture with static and connected actions', function (): void {
    Route::post('/supporter-updates', static fn (): string => 'ok')->name('capell-newsletter.subscribe');

    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/newsletter', false);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('nonprofit', 'newsletter');

    assert($renderer instanceof SectionRenderer);

    $staticHtml = $renderer->render(nonprofitThemeSection('newsletter', [
        'heading' => 'Follow the campaign',
    ]));

    $customHtml = $renderer->render(nonprofitThemeSection('newsletter', [
        'heading' => 'Follow the campaign',
        'formAction' => '/updates',
        'formMethod' => 'post',
    ]));

    CapellCore::forcePackageInstalled('capell-app/newsletter');

    $connectedRegistry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($connectedRegistry);

    $connectedRenderer = $connectedRegistry->sectionRenderer('nonprofit', 'newsletter');

    assert($connectedRenderer instanceof SectionRenderer);

    $connectedHtml = $connectedRenderer->render(nonprofitThemeSection('newsletter', [
        'heading' => 'Supporter briefings',
    ]));

    expect($staticHtml)
        ->toContain('Follow the campaign')
        ->toContain('Static supporter update content is ready.')
        ->toContain('Connect Newsletter or provide a form action to capture supporter updates.')
        ->not->toContain('action="#"')
        ->not->toContain('Subscribe</button>')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($customHtml)
        ->toContain('action="/updates"')
        ->toContain('method="POST"')
        ->toContain('aria-label="Supporter newsletter signup"')
        ->toContain('name="source"')
        ->toContain('theme_nonprofit_newsletter')
        ->toContain('supporter@example.org')
        ->toContain('Subscribe')
        ->not->toContain('action="#"')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($connectedHtml)
        ->toContain('Supporter briefings')
        ->toContain('Connected newsletter capture is ready')
        ->toContain('action="http://localhost/supporter-updates"')
        ->toContain('method="POST"')
        ->toContain('Subscribe')
        ->not->toContain('capell-app/theme-nonprofit')
        ->not->toContain('Filament')
        ->not->toContain('wire:');
});

it('renders translated event labels and the skip-link target', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('nonprofit', 'events');

    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(nonprofitThemeSection('events', [
        'heading' => 'Upcoming supporter events',
    ]));

    $page = view('capell-theme-nonprofit::page', [
        'brand' => new readonly class
        {
            /**
             * @return array<string, string>
             */
            public function tokens(): array
            {
                return ['--theme-primary' => '#166534'];
            }
        },
        'content' => $html,
    ])->render();

    expect($html)
        ->toContain('Campaigns calendar')
        ->toContain('Upcoming supporter events')
        ->not->toContain('Campaigns Calendar')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($page)
        ->toContain('href="#main-content"')
        ->toContain('id="main-content"')
        ->toContain('<main');
});

it('renders campaign story and contact cards from section items before fallbacks', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $campaignRenderer = $registry->sectionRenderer('nonprofit', 'campaigns');
    $storiesRenderer = $registry->sectionRenderer('nonprofit', 'stories');
    $contactRenderer = $registry->sectionRenderer('nonprofit', 'contact');

    assert($campaignRenderer instanceof SectionRenderer);
    assert($storiesRenderer instanceof SectionRenderer);
    assert($contactRenderer instanceof SectionRenderer);

    $campaignHtml = $campaignRenderer->render(nonprofitThemeSection('campaigns', [
        'heading' => 'Appeals in motion',
        'items' => [
            [
                'label' => 'Urgent appeal',
                'title' => 'Food-bank winter fund',
                'summary' => 'A hydrated campaign card with live appeal copy.',
            ],
        ],
    ]));

    $storiesHtml = $storiesRenderer->render(nonprofitThemeSection('stories', [
        'heading' => 'Supporter stories',
        'items' => [
            [
                'type' => 'Volunteer update',
                'title' => 'Community kitchen rota',
                'summary' => 'A hydrated story summary from section content.',
                'meta' => 'Three shifts covered',
            ],
        ],
    ]));

    $contactHtml = $contactRenderer->render(nonprofitThemeSection('contact', [
        'heading' => 'Route supporters clearly',
        'items' => [
            [
                'label' => 'Partnership route',
                'title' => 'Corporate giving team',
                'summary' => 'A hydrated contact card for partnership enquiries.',
            ],
        ],
    ]));

    expect($campaignHtml)
        ->toContain('Appeals in motion')
        ->toContain('Urgent appeal')
        ->toContain('Food-bank winter fund')
        ->toContain('A hydrated campaign card with live appeal copy.')
        ->not->toContain('Awareness')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($storiesHtml)
        ->toContain('Supporter stories')
        ->toContain('Volunteer update')
        ->toContain('Community kitchen rota')
        ->toContain('A hydrated story summary from section content.')
        ->toContain('Three shifts covered')
        ->not->toContain('Community impact story')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($contactHtml)
        ->toContain('Route supporters clearly')
        ->toContain('Partnership route')
        ->toContain('Corporate giving team')
        ->toContain('A hydrated contact card for partnership enquiries.')
        ->not->toContain('Donation questions')
        ->not->toContain('capell-app/theme-nonprofit');
});

it('renders connected campaign progress and event cards from hydrated data', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/campaign-studio');
    CapellCore::forcePackageInstalled('capell-app/events');

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $campaignRenderer = $registry->sectionRenderer('nonprofit', 'campaigns');
    $eventRenderer = $registry->sectionRenderer('nonprofit', 'events');

    assert($campaignRenderer instanceof SectionRenderer);
    assert($eventRenderer instanceof SectionRenderer);

    $campaignHtml = $campaignRenderer->render(nonprofitThemeSection('campaigns', [
        'heading' => 'Live appeals',
        'items' => [
            [
                'label' => 'Active appeal',
                'title' => 'Winter food fund',
                'summary' => 'Hydrated appeal pulled from connected campaign data.',
                'progress' => 72,
                'raised' => '£72k raised',
                'goal' => '£100k goal',
                'action' => ['label' => 'Fund this appeal', 'url' => '/campaigns/winter-food-fund'],
            ],
        ],
    ]));

    $eventHtml = $eventRenderer->render(nonprofitThemeSection('events', [
        'heading' => 'Campaign events',
        'items' => [
            [
                'date' => '12 June',
                'title' => 'Community kitchen day',
                'summary' => 'Connected event listing for supporters.',
                'location' => 'Civic Hall',
                'type' => 'Volunteer',
                'label' => 'Reserve place',
                'url' => '/events/community-kitchen-day',
            ],
        ],
    ]));

    expect($campaignHtml)
        ->toContain('Live appeals')
        ->toContain('Connected campaign workflow is ready.')
        ->toContain('Winter food fund')
        ->toContain('Hydrated appeal pulled from connected campaign data.')
        ->toContain('role="progressbar"')
        ->toContain('aria-valuenow="72"')
        ->toContain('style="width: 72%"')
        ->toContain('£72k raised / £100k goal')
        ->toContain('href="/campaigns/winter-food-fund"')
        ->toContain('Fund this appeal')
        ->not->toContain('capell-app/theme-nonprofit')
        ->not->toContain('Filament')
        ->not->toContain('wire:');

    expect($eventHtml)
        ->toContain('Campaign events')
        ->toContain('Connected events calendar is available.')
        ->toContain('12 June')
        ->toContain('Community kitchen day')
        ->toContain('Connected event listing for supporters.')
        ->toContain('Civic Hall')
        ->toContain('Volunteer')
        ->toContain('href="/events/community-kitchen-day"')
        ->toContain('Reserve place')
        ->not->toContain('capell-app/theme-nonprofit')
        ->not->toContain('Filament')
        ->not->toContain('wire:');
});

it('renders every Nonprofit-owned section with empty or partial section data', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    foreach (nonprofitOwnedSectionKeys() as $sectionKey) {
        $renderer = $registry->sectionRenderer('nonprofit', $sectionKey);

        assert($renderer instanceof SectionRenderer);

        $html = $renderer->render(nonprofitThemeSection($sectionKey, [
            'heading' => 'Partial ' . $sectionKey,
            'items' => [],
            'features' => [],
            'actions' => [],
        ]));

        expect($html)
            ->toContain('theme-section-' . $sectionKey)
            ->toContain('Partial ' . $sectionKey)
            ->not->toContain('capell-app/theme-nonprofit')
            ->not->toContain('Filament')
            ->not->toContain('wire:');
    }
});

it('renders every Nonprofit-owned section anonymously inside the budget without database queries', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/blog', false);
    CapellCore::forcePackageInstalled('capell-app/campaign-studio', false);
    CapellCore::forcePackageInstalled('capell-app/events', false);
    CapellCore::forcePackageInstalled('capell-app/form-builder', false);
    CapellCore::forcePackageInstalled('capell-app/newsletter', false);
    CapellCore::forcePackageInstalled('capell-app/payments', false);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $manifest = nonprofitThemeTestManifest();
    $budgetMilliseconds = nonprofitFrontendRenderBudgetMs($manifest);
    $queryCount = 0;

    DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
        if (str_starts_with(strtolower($query->sql), 'select')) {
            $queryCount++;
        }
    });

    foreach (nonprofitOwnedSectionKeys() as $sectionKey) {
        $renderer = $registry->sectionRenderer('nonprofit', $sectionKey);

        assert($renderer instanceof SectionRenderer);

        $section = nonprofitThemeSection($sectionKey, nonprofitRenderPayload($sectionKey));

        $renderer->render($section);

        $startedAt = hrtime(true);
        $html = $renderer->render($section);
        $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;

        expect($elapsedMilliseconds)->toBeLessThanOrEqual($budgetMilliseconds)
            ->and($html)->not->toContain('capell-app/theme-nonprofit')
            ->and($html)->not->toContain('Filament')
            ->and($html)->not->toContain('wire:');
    }

    expect($queryCount)->toBe(0);
});

/**
 * @param  array<string, mixed>  $viewData
 */
function nonprofitThemeSection(string $key, array $viewData): ThemeSection
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
function nonprofitOwnedSectionKeys(): array
{
    return array_values(array_filter(
        NonprofitThemeServiceProvider::definition()->includedSections,
        static fn (string $sectionKey): bool => ! in_array($sectionKey, ['navigation', 'footer'], true),
    ));
}

/**
 * @return array<string, mixed>
 */
function nonprofitRenderPayload(string $sectionKey): array
{
    $payload = [
        'heading' => 'Anonymous ' . $sectionKey,
        'summary' => 'Anonymous public render summary.',
        'items' => [
            [
                'label' => 'Anonymous label',
                'title' => 'Anonymous nonprofit item',
                'summary' => 'Anonymous nonprofit item summary.',
                'metric' => '42%',
                'meta' => 'Anonymous meta',
            ],
        ],
        'features' => [
            ['title' => 'Anonymous feature', 'description' => 'Anonymous feature summary.'],
        ],
        'actions' => [
            ['label' => 'Anonymous action', 'url' => '#action'],
        ],
    ];

    if ($sectionKey === 'hero') {
        return [
            ...$payload,
            'campaignProgress' => 64,
            'mediaUrl' => 'https://cdn.example.test/nonprofit-hero.jpg',
            'mediaAlt' => 'Anonymous nonprofit hero',
        ];
    }

    if ($sectionKey === 'content-listing') {
        return [
            ...$payload,
            'items' => [
                [
                    'label' => 'Anonymous label',
                    'title' => 'Anonymous nonprofit item',
                    'summary' => 'Anonymous nonprofit item summary.',
                    'metric' => '42%',
                    'meta' => ['Anonymous meta'],
                ],
            ],
        ];
    }

    if ($sectionKey === 'newsletter') {
        return [
            ...$payload,
            'formAction' => '/supporter-updates',
            'formMethod' => 'post',
        ];
    }

    return $payload;
}

/**
 * @return array<string, mixed>
 */
function nonprofitThemeTestManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Nonprofit manifest must decode to an array.');

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
function nonprofitFrontendRenderBudgetMs(array $manifest): float
{
    $budget = data_get($manifest, 'performance.frontendRenderBudgetMs', 20);

    return is_numeric($budget) ? (float) $budget : 20.0;
}
