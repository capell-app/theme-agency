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
use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;

uses(PackagesTestCase::class);

it('defines the Knowledge theme contract', function (): void {
    $definition = KnowledgeThemeServiceProvider::definition();

    expect($definition->key)->toBe('knowledge')
        ->and($definition->package)->toBe('capell-app/theme-knowledge')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('proof')
        ->and($definition->includedSections)->toContain('doc-article')
        ->and($definition->includedSections)->toContain('content-listing')
        ->and($definition->includedSections)->toContain('cta')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('renders standard sections through Knowledge views', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(KnowledgeThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/search');

    $registry = new ThemeRegistry;
    (new KnowledgeThemeServiceProvider($this->app))->boot($registry);

    $featureRenderer = $registry->sectionRenderer('knowledge', 'features');
    $proofRenderer = $registry->sectionRenderer('knowledge', 'proof');
    $listingRenderer = $registry->sectionRenderer('knowledge', 'content-listing');
    $ctaRenderer = $registry->sectionRenderer('knowledge', 'cta');
    $searchRenderer = $registry->sectionRenderer('knowledge', 'search-listing');

    assert($featureRenderer instanceof SectionRenderer);
    assert($proofRenderer instanceof SectionRenderer);
    assert($listingRenderer instanceof SectionRenderer);
    assert($ctaRenderer instanceof SectionRenderer);
    assert($searchRenderer instanceof SectionRenderer);

    $featureHtml = $featureRenderer->render(new FeatureSectionData(
        heading: 'Research pathways',
        summary: 'Feature cards should look like curated library entries.',
        features: [
            ['title' => 'Editorial research', 'description' => 'Collect expert notes and topic paths.', 'type' => 'Guide'],
        ],
    ));

    $proofHtml = $proofRenderer->render(ProofSectionData::from([
        'heading' => 'Library evidence',
        'summary' => 'Proof should use knowledge-base signals.',
        'items' => [
            ['metric' => '420+', 'name' => 'Resources', 'summary' => 'Structured resources stay easy to scan.'],
            ['metric' => '18', 'name' => 'Topics'],
        ],
    ]));

    $listingHtml = $listingRenderer->render(new ContentListingSectionData(
        heading: 'Research archive',
        summary: 'Listings should carry archive and reading queue cues.',
        items: [
            ['title' => 'Content operations guide', 'summary' => 'A practical guide for editorial teams.', 'type' => 'Guide'],
        ],
    ));

    $ctaHtml = $ctaRenderer->render(new CtaSectionData(
        heading: 'Build the library path',
        summary: 'Move readers from discovery to saved resources.',
        actions: [['label' => 'Browse guides', 'url' => '#guides', 'style' => 'primary']],
    ));

    $searchHtml = $searchRenderer->render(new readonly class implements ThemeSection
    {
        public function key(): string
        {
            return 'search-listing';
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
                'heading' => 'Search the archive',
                'summary' => 'Search surfaces should feel like a resource discovery workflow.',
                'filters' => ['Strategy', 'Operations'],
                'items' => [
                    [
                        'title' => 'Research operations guide',
                        'summary' => 'A guide with sources, owner, and next reading route.',
                        'type' => 'Guide',
                        'score' => '97%',
                        'meta' => ['Reviewed', 'Queue'],
                    ],
                ],
            ];
        }
    });

    expect($featureHtml)
        ->toContain('Research pathways')
        ->toContain('Research paths')
        ->toContain('Read next')
        ->not->toContain('capell-app/theme-knowledge');

    expect($proofHtml)
        ->toContain('Library evidence')
        ->toContain('420+')
        ->toContain('Resources')
        ->not->toContain('capell-app/theme-knowledge');

    expect($listingHtml)
        ->toContain('Research archive')
        ->toContain('Content operations guide')
        ->toContain('Saved')
        ->not->toContain('capell-app/theme-knowledge');

    expect($ctaHtml)
        ->toContain('Build the library path')
        ->toContain('Knowledge path')
        ->toContain('knowledge-cta')
        ->toContain('Browse guides')
        ->not->toContain('capell-app/theme-knowledge');

    expect($searchHtml)
        ->toContain('Search the archive')
        ->toContain('knowledge-search-console')
        ->toContain('role="search"')
        ->toContain('method="GET"')
        ->toContain('action="http://localhost/search"')
        ->toContain('type="search"')
        ->toContain('name="q"')
        ->toContain('Search query')
        ->toContain('Submit a keyword to open the full search results experience.')
        ->toContain('knowledge-result-card')
        ->toContain('Facet filters')
        ->toContain('Source map')
        ->toContain('Source signals summarize freshness')
        ->toContain('Research operations guide')
        ->toContain('Relevance')
        ->toContain('97%')
        ->toContain('Connected search index')
        ->not->toContain('capell-app/theme-knowledge');
});

it('renders hydrated hero data through the Knowledge hero view', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(KnowledgeThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new KnowledgeThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('knowledge', 'hero');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(HeroSectionData::from([
        'heading' => 'Open the research library',
        'summary' => 'Hydrated knowledge hero summary.',
        'mediaUrl' => '/images/knowledge-hero.jpg',
        'mediaAlt' => 'Editorial research dashboard',
        'actions' => [
            ['label' => 'Search resources', 'url' => '#search'],
            ['label' => 'Subscribe to digest', 'url' => '#digest'],
        ],
    ]));

    expect($html)
        ->toContain('Editorial command centre')
        ->toContain('Open the research library')
        ->toContain('Hydrated knowledge hero summary.')
        ->toContain('knowledge-hero')
        ->toContain('knowledge-command-panel')
        ->toContain('src="/images/knowledge-hero.jpg"')
        ->toContain('alt="Editorial research dashboard"')
        ->toContain('width="1200"')
        ->toContain('height="750"')
        ->toContain('loading="eager"')
        ->toContain('fetchpriority="high"')
        ->toContain('sizes="(min-width: 1024px) 52vw, 100vw"')
        ->toContain('Search resources')
        ->toContain('Subscribe to digest')
        ->toContain('Reading queue')
        ->not->toContain('capell-app/theme-knowledge');
});

it('renders new premium knowledge layouts through the registry', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(KnowledgeThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new KnowledgeThemeServiceProvider($this->app))->boot($registry);

    $readingPathRenderer = $registry->sectionRenderer('knowledge', 'reading-path');
    $sourceMapRenderer = $registry->sectionRenderer('knowledge', 'source-map');
    $topicIndexRenderer = $registry->sectionRenderer('knowledge', 'topic-index');
    $docArticleRenderer = $registry->sectionRenderer('knowledge', 'doc-article');

    assert($readingPathRenderer instanceof SectionRenderer);
    assert($sourceMapRenderer instanceof SectionRenderer);
    assert($topicIndexRenderer instanceof SectionRenderer);
    assert($docArticleRenderer instanceof SectionRenderer);

    $readingPathHtml = $readingPathRenderer->render(knowledgeThemeSection('reading-path', [
        'heading' => 'Follow a reading path',
        'items' => [
            ['title' => 'Start with fundamentals', 'summary' => 'A staged route through the core resources.'],
        ],
    ]));

    $sourceMapHtml = $sourceMapRenderer->render(knowledgeThemeSection('source-map', [
        'heading' => 'Map the evidence',
        'items' => [
            ['title' => 'Reviewed sources', 'summary' => 'Citation and freshness proof for editorial teams.'],
        ],
    ]));

    $topicIndexHtml = $topicIndexRenderer->render(knowledgeThemeSection('topic-index', [
        'heading' => 'Browse topic routes',
        'items' => [
            ['title' => 'Operations', 'summary' => 'Dense topic routing for a library-grade index.'],
        ],
    ]));

    $docArticleHtml = $docArticleRenderer->render(knowledgeThemeSection('doc-article', [
        'title' => 'Configure knowledge workflows',
        'summary' => 'A docs page needs sidebar routing, breadcrumbs, and in-page anchors.',
        'category' => 'Operations guide',
        'updatedAt' => 'June 5, 2026',
        'readingTime' => '11 min',
        'breadcrumbs' => [
            ['label' => 'Docs', 'url' => '/docs'],
            ['label' => 'Operations'],
        ],
        'sidebarItems' => [
            ['label' => 'Operations', 'url' => '/docs/operations', 'active' => true],
            ['label' => 'Editorial review', 'url' => '/docs/review'],
        ],
        'tocItems' => [
            ['label' => 'Plan the library', 'url' => '#plan-the-library'],
            ['label' => 'Publish the article', 'url' => '#publish-the-article'],
        ],
        'contentHtml' => '<h3 id="plan-the-library">Plan the library</h3><p>Keep the article body constrained and readable.</p>',
    ]));

    expect($readingPathHtml)
        ->toContain('Follow a reading path')
        ->toContain('Start with fundamentals')
        ->not->toContain('capell-app/theme-knowledge');

    expect($sourceMapHtml)
        ->toContain('Map the evidence')
        ->toContain('Reviewed sources')
        ->not->toContain('capell-app/theme-knowledge');

    expect($topicIndexHtml)
        ->toContain('Browse topic routes')
        ->toContain('Operations')
        ->not->toContain('capell-app/theme-knowledge');

    expect($docArticleHtml)
        ->toContain('Configure knowledge workflows')
        ->toContain('A docs page needs sidebar routing, breadcrumbs, and in-page anchors.')
        ->toContain('Operations guide')
        ->toContain('June 5, 2026')
        ->toContain('11 min')
        ->toContain('Docs')
        ->toContain('Editorial review')
        ->toContain('On this page')
        ->toContain('Plan the library')
        ->toContain('Keep the article body constrained and readable.')
        ->not->toContain('capell-app/theme-knowledge');
});

it('renders translated and data-driven knowledge author and topic sections', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(KnowledgeThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new KnowledgeThemeServiceProvider($this->app))->boot($registry);

    $authorsRenderer = $registry->sectionRenderer('knowledge', 'authors');
    $topicHubsRenderer = $registry->sectionRenderer('knowledge', 'topic-hubs');

    assert($authorsRenderer instanceof SectionRenderer);
    assert($topicHubsRenderer instanceof SectionRenderer);

    $defaultAuthorsHtml = $authorsRenderer->render(knowledgeThemeSection('authors', [
        'heading' => 'Knowledge authors',
    ]));

    $customAuthorsHtml = $authorsRenderer->render(knowledgeThemeSection('authors', [
        'heading' => 'Specialist authors',
        'items' => [
            ['title' => 'Docs Engineering', 'summary' => 'Maintains API references and release notes.'],
            ['name' => 'Support Research', 'description' => 'Turns support evidence into reader paths.'],
        ],
    ]));

    $defaultTopicsHtml = $topicHubsRenderer->render(knowledgeThemeSection('topic-hubs', [
        'heading' => 'Topic hubs',
    ]));

    $customTopicsHtml = $topicHubsRenderer->render(knowledgeThemeSection('topic-hubs', [
        'heading' => 'Documentation routes',
        'items' => [
            ['title' => 'API Guides', 'summary' => 'Endpoint walkthroughs and integration patterns.'],
            ['name' => 'Release Notes', 'description' => 'Version changes and upgrade guidance.'],
        ],
    ]));

    expect($defaultAuthorsHtml)
        ->toContain('Knowledge authors')
        ->toContain('Editorial Team')
        ->toContain('Subject-matter writing and review teams.');

    expect($customAuthorsHtml)
        ->toContain('Specialist authors')
        ->toContain('Docs Engineering')
        ->toContain('Support Research')
        ->not->toContain('Editorial Team')
        ->not->toContain('Growth Ops');

    expect($defaultTopicsHtml)
        ->toContain('Topic hubs')
        ->toContain('Strategy')
        ->toContain('Design systems')
        ->toContain('Decision records, positioning notes, and planning guides.');

    expect($customTopicsHtml)
        ->toContain('Documentation routes')
        ->toContain('API Guides')
        ->toContain('Release Notes')
        ->not->toContain('Growth library')
        ->not->toContain('Operations');
});

/**
 * @param  array<string, mixed>  $viewData
 */
function knowledgeThemeSection(string $key, array $viewData): ThemeSection
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
