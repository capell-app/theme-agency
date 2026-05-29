<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
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

    $featureHtml = $registry
        ->sectionRenderer('knowledge', 'features')
        ->render(new FeatureSectionData(
            heading: 'Research pathways',
            summary: 'Feature cards should look like curated library entries.',
            features: [
                ['title' => 'Editorial research', 'summary' => 'Collect expert notes and topic paths.', 'type' => 'Guide'],
            ],
        ));

    $proofHtml = $registry
        ->sectionRenderer('knowledge', 'proof')
        ->render(ProofSectionData::from([
            'heading' => 'Library evidence',
            'summary' => 'Proof should use knowledge-base signals.',
            'items' => [
                ['metric' => '420+', 'name' => 'Resources', 'summary' => 'Structured resources stay easy to scan.'],
            ],
        ]));

    $listingHtml = $registry
        ->sectionRenderer('knowledge', 'content-listing')
        ->render(new ContentListingSectionData(
            heading: 'Research archive',
            summary: 'Listings should carry archive and reading queue cues.',
            items: [
                ['title' => 'Content operations guide', 'summary' => 'A practical guide for editorial teams.', 'type' => 'Guide'],
            ],
        ));

    $ctaHtml = $registry
        ->sectionRenderer('knowledge', 'cta')
        ->render(new CtaSectionData(
            heading: 'Build the library path',
            summary: 'Move readers from discovery to saved resources.',
            actions: [['label' => 'Browse guides', 'url' => '#guides', 'style' => 'primary']],
        ));

    $searchHtml = $registry
        ->sectionRenderer('knowledge', 'search-listing')
        ->render(new readonly class implements ThemeSection
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
        ->toContain('Browse guides')
        ->not->toContain('capell-app/theme-knowledge');

    expect($searchHtml)
        ->toContain('Search the archive')
        ->toContain('Facet filters')
        ->toContain('Source map')
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

    $html = $renderer->render(HeroSectionData::from([
        'heading' => 'Open the research library',
        'summary' => 'Hydrated knowledge hero summary.',
        'actions' => [
            ['label' => 'Search resources', 'url' => '#search'],
            ['label' => 'Subscribe to digest', 'url' => '#digest'],
        ],
    ]));

    expect($html)
        ->toContain('Editorial command centre')
        ->toContain('Open the research library')
        ->toContain('Hydrated knowledge hero summary.')
        ->toContain('Search resources')
        ->toContain('Subscribe to digest')
        ->toContain('Reading queue')
        ->not->toContain('capell-app/theme-knowledge');
});
