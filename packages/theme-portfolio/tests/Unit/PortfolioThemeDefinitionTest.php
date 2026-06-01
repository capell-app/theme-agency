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
            return ['section' => (object) $this->viewData];
        }
    };
}
